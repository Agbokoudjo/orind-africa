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

namespace App\Infrastructure\Listener;

use Doctrine\ORM\Events;
use App\Domain\Log\ActivityLog;
use App\Domain\Log\Enum\ActivityAction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use App\Domain\User\Model\BaseUserInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Log\Command\ActivityLogCommand;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Application\Security\SecurityContextInterface;
use App\Domain\User\Service\Resolver\UserTypeResolver;
use App\Application\Queue\AsyncMethodDispatcherInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use App\Application\UseCase\CommandHandler\ActivityLogCommandHandler;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AsDoctrineListener(event: Events::onFlush)]
final class DoctrineActivityLogListener 
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly SecurityContextInterface $security,
        private readonly AsyncMethodDispatcherInterface $enqueueActivity,
        
    ) {}

    public function onFlush(OnFlushEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();
        \assert($objectManager instanceof EntityManagerInterface);
        $uow = $objectManager->getUnitOfWork();

        $request = $this->requestStack->getMainRequest();

        // Si aucune requête HTTP (ex: console ou worker), on arrête pour l'instant. 
        // L'audit des Workers est géré différemment (voir section 3).
        if (!$request) {
            return;
        }

        $user = $this->security->getCurrentUser();
        $userContext = $this->extractUserContext($user);
        $ipAddress = $request->getClientIp();

        // 1. Enregistrement des Créations (INSERT)
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->logChanges($entity, ActivityAction::ENTITY_CREATE, $userContext, $ipAddress, $request, $uow->getEntityChangeSet($entity));
        }

        // 2. Enregistrement des Mises à jour (UPDATE)
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->logChanges($entity, ActivityAction::ENTITY_UPDATE, $userContext, $ipAddress, $request, $uow->getEntityChangeSet($entity));
        }

        // 3. Enregistrement des Suppressions (DELETE)
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            // Note: Lors d'une suppression, getEntityChangeSet retourne un tableau vide.
            $this->logChanges($entity, ActivityAction::ENTITY_DELETE, $userContext, $ipAddress, $request, $uow->getOriginalEntityData($entity));
        }
    }

    /**
     * Construit et dispatche l'ActivityLog.
     */
    private function logChanges(
        object $entity,
        ActivityAction $action,
        array $userContext,
        string $ipAddress,
        Request $request,
        array $data
    ): void{

        // ⚠️ Règle d'exclusion : NE JAMAIS auditer les modifications de la table d'audit elle-même.
        // Si l'entité est votre ActivityLog, on ignore.
        if ($entity instanceof ActivityLog) {
            return;
        }

        // Obtenir l'ID de l'entité (doit avoir une méthode getId())
        $entityId = \method_exists($entity, 'getId') ? $entity->getId() : 'N/A';
        // Filtrer les champs potentiellement sensibles ou les objets Doctrine
        $changes = $this->sanitizeChanges($data, $action);

        $activityLogCommand=new ActivityLogCommand(
            userContext: $userContext,
            ipAddress: $ipAddress,
            action: $action,
            route: $request->attributes->get('_route') ?? 'N/A',
            method: $request->getMethod(),
            context: [
                'entity_class' => $entity::class,
                'entity_id' => $entityId,
                'changes' => $changes,
                'description' => $this->generateDescription($entity, $action, $entityId), // Description lisible
            ]
        );

        $this->enqueueActivity->dispatch(ActivityLogCommandHandler::class,'handle',[$activityLogCommand]);
    }

    /**
     * Nettoie les données de changement pour l'enregistrement (filtre les mots de passe et relations complexes).
     */
    private function sanitizeChanges(array $data, ActivityAction $action): array{
        $sanitized = [];

        // Pour CREATE et UPDATE, $data est un tableau de changements [old, new]
        // Pour DELETE, $data est le tableau des données originales
        foreach ($data as $field => $values) {
            if (str_contains(strtolower($field), 'password') || str_contains(strtolower($field), 'token')) {
                $sanitized[$field] = ['old' => '***FILTERED***', 'new' => '***FILTERED***'];
                continue;
            }

            if ($action === ActivityAction::ENTITY_UPDATE) {
                // Pour les mises à jour, on enregistre [ancienne valeur, nouvelle valeur]
                $oldValue = is_object($values[0]) && method_exists($values[0], '__toString') ? (string) $values[0] : $values[0];
                $newValue = is_object($values[1]) && method_exists($values[1], '__toString') ? (string) $values[1] : $values[1];

                // Si la valeur est une entité Doctrine, enregistrez l'ID pour éviter les problèmes de sérialisation
                $oldValue = $oldValue instanceof \Doctrine\Common\Collections\Collection ? 'Collection' : $oldValue;
                $newValue = $newValue instanceof \Doctrine\Common\Collections\Collection ? 'Collection' : $newValue;

                $sanitized[$field] = ['old' => $oldValue, 'new' => $newValue];
            } elseif ($action === ActivityAction::ENTITY_CREATE || $action === ActivityAction::ENTITY_DELETE) {
                // Pour la création/suppression, on enregistre les données de l'entité.
                // On simplifie les relations à leur ID si possible.
                $value = is_object($values) && method_exists($values, '__toString') ? (string) $values : $values;
                $sanitized[$field] = $value;
            }
        }

        return $sanitized;
    }

    // Méthode simple pour extraire les données de l'utilisateur (à réutiliser depuis ActivitySubscriber)
    private function extractUserContext(?BaseUserInterface $user): array
    {
        $userContext = ['id' => null, 'email' => 'anonymous', 'type' => 'guest'];

        if ($user && $user instanceof BaseUserInterface) {
            $userContext = [
                'id' => $user->getId(),
                'email' => \method_exists($user, 'getEmail') ? $user->getEmail() : 'N/A',
                'username' => $user->getUsername(),
                'role' => $user->getRolePrincipal(),
                'type' => UserTypeResolver::resolveFromUser($user)->value
            ];
        }
        return $userContext;
    }

    /**
     * Génère une description lisible pour l'interface d'audit.
     */
    private function generateDescription(object $entity, ActivityAction $action, string|int|null $id): string
    {
        $entityName = (new \ReflectionClass($entity))->getShortName();

        return match ($action) {
            ActivityAction::ENTITY_CREATE => sprintf('Création de l\'entité %s (ID: %s)', $entityName, $id),
            ActivityAction::ENTITY_UPDATE => sprintf('Mise à jour de l\'entité %s (ID: %s)', $entityName, $id),
            ActivityAction::ENTITY_DELETE => sprintf('Suppression de l\'entité %s (ID: %s)', $entityName, $id),
            default => sprintf('Action non spécifiée sur %s', $entityName),
        };
    }
}
