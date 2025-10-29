<?php

declare(strict_types=1);

/*
 * This file is part of the project by AGBOKOUDJO Franck.
 *
 * (c) AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * Phone: +229 01 67 25 18 86
 * LinkedIn: https://www.linkedin.com/in/internationales-web-apps-services-120520193/
 * Github: https://github.com/Agbokoudjo/
 * Company: INTERNATIONALES WEB APPS & SERVICES
 *
 * For more information, please feel free to contact the author.
 */

namespace App\UI\Http\EventSubscriber;

use Psr\Log\LoggerInterface;
use App\Domain\Log\Enum\ActivityAction;
use App\Domain\User\Model\BaseUserInterface;
use App\Domain\Log\Command\ActivityLogCommand;
use App\Infrastructure\Utility\UserContextTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\SecurityEvents;
use App\Application\UseCase\User\LoggerLoginUseCase;
use App\Domain\User\Service\Resolver\UserTypeResolver;
use App\Application\Queue\AsyncMethodDispatcherInterface;
use App\Domain\User\Exception\UnresolvableUserTypeException;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Application\UseCase\CommandHandler\ActivityLogCommandHandler;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Subscriber pour l'enregistrement des tentatives de connexion (succès et échecs).
 * 
 * Responsabilités :
 * - Enregistrer la date/IP de dernière connexion réussie
 * - Logger les connexions réussies dans le système d'activité
 * - Logger les tentatives de connexion échouées pour analyse de sécurité
 * -Enregistre le timestamp de connexion dans la session.
 * 
 * Tous les traitements sont effectués de manière asynchrone pour ne pas
 * impacter les performances de l'authentification.
 *
 * @internal Ce subscriber est appelé automatiquement par Symfony Security
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package App\UI\Http\EventSubscriber
 */
final class LoginSubscriber implements EventSubscriberInterface{

    use UserContextTrait ;

    /**
     * Clé utilisée pour stocker le timestamp dans la session.
     */
    private const SESSION_KEY = '_security_login_time';
    
    public function __construct(
        private readonly AsyncMethodDispatcherInterface $asyncDispatcher,
        private readonly ParameterBagInterface $parameterBag,
        private readonly ?LoggerInterface $logger = null
    ) {}

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Connexion réussie
            SecurityEvents::INTERACTIVE_LOGIN => [
                ['onSessionLoginTime',100],
                ['onLastLoginUpdate', 0],      // Mise à jour lastLogin en priorité
                ['onLoginSuccessActivity', -1], // Logging après
            ],
            // Connexion échouée
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    /**
     * Enregistre le timestamp de connexion dans la session.
     * 
     * Ce listener simple stocke l'heure exacte de connexion de l'utilisateur
     * dans la session Symfony. Cette information est utilisée par LogoutSubscriber
     * pour calculer la durée totale de la session lors de la déconnexion.
     * 
     *  S'exécute avec une haute priorité (100) pour garantir que le timestamp
     * est enregistré avant tout autre traitement.
     *
     * @param InteractiveLoginEvent $event L'événement de connexion
     * 
     * @return void
     * 
     * Cas d'usage :
     * - Calcul de la durée de session
     * - Statistiques d'engagement utilisateur
     * - Détection de sessions anormalement courtes/longues
     * - Audit de sécurité
     *
     */
    public function onSessionLoginTime(InteractiveLoginEvent $event): void{
        $session = $event->getRequest()->getSession();

        if ($session === null) {
            // Pas de session disponible (cas rare)
            return;
        }

        // Stocker le timestamp actuel (UNIX timestamp en secondes)
        $session->set(self::SESSION_KEY, time());
    }

    /**
     * Met à jour la date et l'IP de dernière connexion de l'utilisateur.
     * 
     * Exécuté en premier (priorité 0) pour garantir que la date est
     * enregistrée même si le logging d'activité échoue.
     *
     * @param InteractiveLoginEvent $event L'événement de connexion
     * 
     * @return void
     */
    public function onLastLoginUpdate(InteractiveLoginEvent $event): void
    {
        if (!$this->isListenerEnabled()) {
            return;
        }

        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof BaseUserInterface) {
            $this->logger?->debug('Utilisateur non valide pour mise à jour lastLogin', [
                'user_class' => $user ? $user::class : 'null',
            ]);
            return;
        }

        try {
            $this->asyncDispatcher->dispatch(
                LoggerLoginUseCase::class,
                'recordLogin',
                [$user, $event->getRequest()->getClientIp()]
            );

            $this->logger?->info('Mise à jour lastLogin dispatchée', [
                'user_id' => $user->getId(),
                'ip' => $event->getRequest()->getClientIp(),
            ]);
        } catch (\Exception $e) {
            $this->logger?->error('Échec dispatch mise à jour lastLogin', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);
        }
    } 

    /**
     * Enregistre l'activité de connexion réussie dans le système de logs.
     * 
     * Exécuté en second (priorité -1) après la mise à jour de lastLogin.
     *
     * @param InteractiveLoginEvent $event L'événement de connexion
     * 
     * @return void
     */
    public function onLoginSuccessActivity(InteractiveLoginEvent $event): void
    {
        if (!$this->isListenerEnabled()) {
            return;
        }

        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof BaseUserInterface) {
            $this->logger?->debug('Utilisateur non valide pour log activité connexion', [
                'user_class' => $user ? $user::class : 'null',
            ]);
            return;
        }

        try {
            $userContext = $this->extractUserContext($user);
            $request = $event->getRequest();

            $loginCommand = new ActivityLogCommand(
                userContext: $userContext,
                ipAddress: $request->getClientIp() ?? 'unknown',
                action: ActivityAction::LOGIN_SUCCESS,
                route: $request->attributes->get('_route') ?? 'N/A',
                method: $request->getMethod(),
                context: [
                    'user_agent' => $request->headers->get('User-Agent'),
                    'session_id' => $request->getSession()?->getId(),
                    'referer' => $request->headers->get('Referer'),
                ]
            );

            $this->dispatchActivityLog($loginCommand);

            $this->logger?->info('Connexion réussie enregistrée', [
                'user_id' => $user->getId(),
                'ip' => $request->getClientIp(),
            ]);
        } catch (\Exception $e) {
            $this->logger?->error('Échec log activité connexion réussie', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Enregistre les tentatives de connexion échouées pour analyse de sécurité.
     * 
     * Permet de détecter :
     * - Les tentatives de brute force
     * - Les comptes compromis
     * - Les attaques par dictionnaire
     *
     * @param LoginFailureEvent $event L'événement d'échec de connexion
     * 
     * @return void
     */
    public function onLoginFailure(LoginFailureEvent $event): void
    {
        if (!$this->isListenerEnabled()) {
            return;
        }

        try {
            $request = $event->getRequest();
            $exception = $event->getException();
            $passport = $event->getPassport();

            // Récupérer l'identifiant tenté (email ou username)
            $usernameAttempted = $passport?->getUser()?->getUserIdentifier() ?? 'N/A';

            // Contexte guest car connexion échouée
            $userContext = $this->extractUserContext(null);

            $failureCommand = new ActivityLogCommand(
                userContext: $userContext,
                ipAddress: $request->getClientIp() ?? 'unknown',
                action: ActivityAction::LOGIN_FAILURE,
                route: $request->attributes->get('_route') ?? 'N/A',
                method: $request->getMethod(),
                context: [
                    'username_attempted' => $usernameAttempted,
                    'failure_reason' => $exception->getMessage(),
                    'exception_type' => $exception::class,
                    'user_agent' => $request->headers->get('User-Agent'),
                ]
            );

            $this->dispatchActivityLog($failureCommand);

            $this->logger?->warning('Tentative de connexion échouée', [
                'username' => $usernameAttempted,
                'ip' => $request->getClientIp(),
                'reason' => $exception->getMessage(),
            ]);
        } catch (\Exception $e) {
            $this->logger?->error('Échec log tentative connexion échouée', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Dispatche une commande d'activité de manière asynchrone.
     *
     * @param ActivityLogCommand $command La commande à dispatcher
     * 
     * @return void
     */
    private function dispatchActivityLog(ActivityLogCommand $command): void
    {
        $this->asyncDispatcher->dispatch(
            ActivityLogCommandHandler::class,
            'handle',
            [$command]
        );
    }

    /**
     * Vérifie si le listener est activé via la configuration.
     *
     * @return bool True si activé, false sinon
     */
    private function isListenerEnabled(): bool
    {
        $enabled = $this->parameterBag->get('app.execute_listener');

        if (!$enabled) {
            $this->logger?->debug('Listener désactivé via configuration app.execute_listener');
        }

        return (bool) $enabled;
    }
}