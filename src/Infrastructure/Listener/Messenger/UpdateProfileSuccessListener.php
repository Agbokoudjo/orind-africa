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

namespace App\Infrastructure\Listener\Messenger;

use Psr\Log\LoggerInterface;
use App\Domain\User\Model\BaseUserInterface;
use App\Application\Service\SecureTokenService;
use App\Domain\User\Message\UpdateUserProfileCommand;
use App\Application\Queue\Message\ServiceMethodMessage;
use App\Domain\User\Exception\UserManagerNotFoundException;
use App\Domain\User\Exception\EmailAlreadyVerifiedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use App\Domain\User\Service\Registry\UserManagerRegistryInterface;
use App\Application\UseCase\CommandHandler\User\UpdateUserProfileCommandHandler;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final readonly class UpdateProfileSuccessListener implements EventSubscriberInterface
{
    public function __construct(
        private UserManagerRegistryInterface $managerRegistry,
        private SecureTokenService $tokenGenerateService,
        private LoggerInterface $logger
    ){}

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageHandledEvent::class=> 'onWorkerMessageHandled'
        ];
    }

    public function onWorkerMessageHandled(WorkerMessageHandledEvent $event): void{

        // 1. Vérifier si le message traité est celui qui nous intéresse
        $message = $event->getEnvelope()->getMessage();

        if(!$message instanceof ServiceMethodMessage){ return ;}

        // 2. Vérifier si le message exécuté était bien notre CommandHandler de profil

        if ($message->getServiceName() !== UpdateUserProfileCommandHandler::class 
            || $message->getMethod() !== 'handler') {
            return;
        }

        // 3. Extraire l'objet UpdateUserProfileCommand réel
        $params = $message->getParams();
        if (empty($params) || !$params[0] instanceof UpdateUserProfileCommand) {

            //  Loguer que l'argument attendu n'est pas présent 
            $this->logger->error(
                'Échec de l\'exécution asynchrone : Le CommandHandler de profil a été appelé sans l\'objet de commande attendu.',
                [
                    'expected_command' => UpdateUserProfileCommand::class,
                    'service' => $message->getServiceName(),
                    'method' => $message->getMethod(),
                    'params_count' => count($params),
                    'params_type' => empty($params) ? 'aucun' : get_debug_type($params[0]),
                ]
            );
            return;
        }

        /** @var UpdateUserProfileCommand $command */
        $command = $params[0];

        // --- Déclenchement de l'Email (Logique de Succès) ---
        // 4. Récupérer l'Entité mise à jour
        try {
            $manager = $this->managerRegistry->getByUserType($command->getUserType());

            /**
             * @var BaseUserInterface
             */
            $user = $manager->find($command->getUserId());
        } catch (UserManagerNotFoundException $e) {
            // Cette erreur est un problème de configuration interne (Dev)
            // Erreur de configuration/Dev. Doit être relancée.
            $this->logAndThrowCriticalException($e, $command);
            throw $e;
        }

        // 5. Vérifier si l'utilisateur est présent et non vérifié
        if (null === $user || $user->isIsEmailVerified()) {
            $this->logIgnoredSend($user, $command);
            return;
        }
       
        try {
            // Le service s'occupe de la génération, du hachage, de la persistance, et du dispatch de l'événement d'email.
            $this->tokenGenerateService->generateEmailConfirmationToken($user);
        } catch (EmailAlreadyVerifiedException $e) {
            // Cas théorique : L'utilisateur est vérifié entre les points 5 et 6.
            // On log et on arrête. Pas de relance.
            $this->logger->warning(
                'Tentative de génération de token pour un email déjà vérifié après le contrôle asynchrone.',
                ['exception' => $e, 'user_id' => $command->getUserId()]
            );

            return;

        } catch (\RuntimeException $e) {
            // Erreur liée à l'utilisateur (Ex: Cool-down / Rate Limiting).
            // L'utilisateur ne peut rien faire; le message ne doit pas être rejoué car le temps d'attente
            // du cool-down sera dépassé lors du prochain rejeu. On log et on arrête.
            $this->logger->notice(
                'Échec d\'envoi d\'email en raison du Cool-down/Rate Limiting.',
                ['exception' => $e, 'user_id' => $command->getUserId(), 'type' => 'Cool-down']
            );

            return;

        } catch (\InvalidArgumentException $e) {
            // Erreur de logique de développement (Ex: Email vide, longueur invalide). 
            // Doit être traitée comme une erreur critique de configuration/code.
            $this->logAndThrowCriticalException($e, $command);
        } catch (\Exception $e) {
            // Toute autre erreur non prévue (Ex: Problème de BDD lors du save, erreur de hachage).
            // Le Worker doit échouer et retenter.
            $this->logAndThrowCriticalException($e, $command, 'Erreur inattendue lors de la génération du token.');
        }
    }

    // --- METHODES HELPER ---

    /** Gère le logging critique et la relance pour les erreurs de développement. */
    private function logAndThrowCriticalException(\Throwable $e, UpdateUserProfileCommand $command, string $message = 'Erreur critique de développement.'): never
    {
        $this->logger->critical($message, [
            'exception' => $e,
            'user_id' => $command->getUserId(),
            'user_type' => $command->getUserType()->name,
            'code' => 'CRIT_002'
        ]);
        throw $e;
    }

    /** Gère le logging pour les envois d'email ignorés. */
    private function logIgnoredSend(?BaseUserInterface $user, UpdateUserProfileCommand $command): void
    {
        $reason = null === $user ? 'utilisateur non trouvé' : 'utilisateur déjà vérifié';

        $this->logger->notice(
            'Envoi de l\'e-mail de vérification ignoré : le traitement asynchrone n\'est plus nécessaire.',
            [
                'reason' => $reason,
                'user_id' => $command->getUserId(),
                'user_type' => $command->getUserType()->name,
            ]
        );
    }
}