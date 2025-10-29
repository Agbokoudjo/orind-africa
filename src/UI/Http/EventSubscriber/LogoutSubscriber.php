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

use App\Application\Queue\AsyncMethodDispatcherInterface;
use App\Application\UseCase\CommandHandler\ActivityLogCommandHandler;
use App\Domain\Log\Command\ActivityLogCommand;
use App\Domain\Log\Enum\ActivityAction;
use App\Domain\User\Exception\UnresolvableUserTypeException;
use App\Domain\User\Model\BaseUserInterface;
use App\Domain\User\Service\Resolver\UserTypeResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Subscriber pour l'enregistrement des déconnexions utilisateur.
 * 
 * Responsabilités :
 * - Logger toutes les déconnexions (volontaires ou par expiration de session)
 * - Calculer et enregistrer la durée de la session
 * - Capturer le contexte de déconnexion (IP, user agent, etc.)
 * - Détecter les déconnexions anormales (sessions très courtes)
 * 
 * Le traitement est effectué de manière asynchrone pour ne pas
 * impacter les performances de la déconnexion.
 *
 * @internal Ce subscriber est appelé automatiquement par Symfony Security
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package App\UI\Http\EventSubscriber
 */
final class LogoutSubscriber implements EventSubscriberInterface
{
    /**
     * Durée minimale de session considérée comme normale (en secondes).
     * Une session plus courte peut indiquer un problème technique ou de sécurité.
     */
    private const MIN_NORMAL_SESSION_DURATION = 30;

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
            LogoutEvent::class => 'onLogout',
        ];
    }

    /**
     * Enregistre les déconnexions utilisateur avec contexte complet.
     * 
     * Capture les informations suivantes :
     * - Identité de l'utilisateur (si disponible)
     * - Heure et IP de déconnexion
     * - Durée de la session (si calculable)
     * - Contexte de la requête (user agent, route, etc.)
     * - Type de déconnexion (volontaire, expiration, etc.)
     * 
     * Gère également les cas particuliers :
     * - Déconnexion après expiration de session (user = null)
     * - Sessions anormalement courtes (possibles problèmes)
     * - Déconnexions multiples simultanées
     *
     * @param LogoutEvent $event L'événement de déconnexion
     * 
     * @return void
     */
    public function onLogout(LogoutEvent $event): void
    {
        if (!$this->isListenerEnabled()) {
            return;
        }

        try {
            $request = $event->getRequest();
            $token = $event->getToken();

            // Récupérer l'utilisateur depuis le token (peut être null si session expirée)
            $user = $token?->getUser();

            // Extraire le contexte utilisateur
            if (!$user instanceof BaseUserInterface) {
                $this->logAnonymousLogout($request);
                $userContext = $this->extractUserContext(null);
            } else {
                $userContext = $this->extractUserContext($user);
            }

            // Calculer la durée de session
            $sessionMetadata = $this->extractSessionMetadata($request);

            // Construire la commande de log
            $logoutCommand = new ActivityLogCommand(
                userContext: $userContext,
                ipAddress: $request->getClientIp() ?? 'unknown',
                action: ActivityAction::LOGOUT,
                route: $request->attributes->get('_route') ?? 'N/A',
                method: $request->getMethod(),
                context: [
                    'user_agent' => $request->headers->get('User-Agent'),
                    'session_id' => $sessionMetadata['session_id'],
                    'session_duration_seconds' => $sessionMetadata['duration'],
                    'session_started_at' => $sessionMetadata['started_at'],
                    'logout_type' => $user === null ? 'expired' : 'voluntary',
                    'referer' => $request->headers->get('Referer'),
                ]
            );

            // Dispatcher de manière asynchrone
            $this->dispatchActivityLog($logoutCommand);

            // Logging approprié selon le type de déconnexion
            if ($user instanceof BaseUserInterface) {
                $this->logSuccessfulLogout($user, $request, $sessionMetadata['duration']);
            }

            // Alerter si session anormalement courte
            if (
                $sessionMetadata['duration'] !== null
                && $sessionMetadata['duration'] < self::MIN_NORMAL_SESSION_DURATION
                && $user instanceof BaseUserInterface
            ) {
                $this->logger?->warning('Session anormalement courte détectée', [
                    'user_id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'duration_seconds' => $sessionMetadata['duration'],
                    'ip' => $request->getClientIp(),
                ]);
            }
        } catch (\Exception $e) {
            $this->logger?->error('Échec de l\'enregistrement de la déconnexion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Extrait le contexte utilisateur pour le logging.
     *
     * @param BaseUserInterface|null $user L'utilisateur (null si session expirée)
     * 
     * @return array<string, mixed> Le contexte utilisateur
     */
    private function extractUserContext(?BaseUserInterface $user): array
    {
        if ($user === null) {
            return [
                'id' => null,
                'email' => 'anonymous',
                'username' => 'anonymous',
                'role' => 'ROLE_ANONYMOUS',
                'type' => 'guest',
            ];
        }

        try {
            $userType = UserTypeResolver::resolveFromUser($user);

            return [
                'id' => $user->getId(),
                'email' => method_exists($user, 'getEmail') ? $user->getEmail() : 'N/A',
                'username' => $user->getUsername(),
                'role' => $user->getRolePrincipal(),
                'type' => $userType->value,
            ];
        } catch (UnresolvableUserTypeException $e) {
            $this->logger?->warning('Impossible de résoudre le type utilisateur lors de la déconnexion', [
                'user_class' => $user::class,
                'error' => $e->getMessage(),
            ]);

            return [
                'id' => $user->getId(),
                'email' => method_exists($user, 'getEmail') ? $user->getEmail() : 'N/A',
                'username' => $user->getUsername(),
                'role' => $user->getRolePrincipal(),
                'type' => 'unknown',
            ];
        }
    }

    /**
     * Extrait les métadonnées de session (durée, ID, heure de début).
     *
     * @param Request $request La requête de déconnexion
     * 
     * @return array{session_id: string|null, duration: int|null, started_at: string|null}
     */
    private function extractSessionMetadata(Request $request): array
    {
        $session = $request->hasSession() ? $request->getSession() : null;

        if ($session === null) {
            return [
                'session_id' => null,
                'duration' => null,
                'started_at' => null,
            ];
        }

        $sessionId = $session->getId();
        $loginTime = $session->get('_security_login_time');
        $duration = null;
        $startedAt = null;

        if ($loginTime !== null) {
            $duration = time() - $loginTime;
            $startedAt = date('Y-m-d H:i:s', $loginTime);
        }

        return [
            'session_id' => $sessionId,
            'duration' => $duration,
            'started_at' => $startedAt,
        ];
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
     * Log une déconnexion réussie avec contexte utilisateur.
     *
     * @param BaseUserInterface $user L'utilisateur déconnecté
     * @param Request $request La requête de déconnexion
     * @param int|null $sessionDuration Durée de la session en secondes
     * 
     * @return void
     */
    private function logSuccessfulLogout(
        BaseUserInterface $user,
        Request $request,
        ?int $sessionDuration
    ): void {
        $this->logger?->info('Déconnexion utilisateur enregistrée', [
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => method_exists($user, 'getEmail') ? $user->getEmail() : 'N/A',
            'ip' => $request->getClientIp(),
            'session_duration' => $sessionDuration !== null
                ? $this->formatDuration($sessionDuration)
                : 'N/A',
            'route' => $request->attributes->get('_route') ?? 'N/A',
        ]);
    }

    /**
     * Log une déconnexion anonyme (session expirée).
     *
     * @param Request $request La requête de déconnexion
     * 
     * @return void
     */
    private function logAnonymousLogout(Request $request): void
    {
        $this->logger?->debug('Déconnexion anonyme ou session expirée', [
            'ip' => $request->getClientIp(),
            'route' => $request->attributes->get('_route') ?? 'N/A',
        ]);
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
            $this->logger?->debug('LogoutSubscriber désactivé via configuration app.execute_listener');
        }

        return (bool) $enabled;
    }

    /**
     * Formate une durée en secondes en format lisible.
     *
     * @param int $seconds Durée en secondes
     * 
     * @return string Durée formatée (ex: "2h 15m 30s")
     */
    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return sprintf('%ds', $seconds);
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = sprintf('%dh', $hours);
        }

        if ($minutes > 0) {
            $parts[] = sprintf('%dm', $minutes);
        }

        if ($secs > 0 || empty($parts)) {
            $parts[] = sprintf('%ds', $secs);
        }

        return implode(' ', $parts);
    }
}
