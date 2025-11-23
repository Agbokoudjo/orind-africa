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

namespace App\Infrastructure\EventListener;

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
 * Listener Doctrine pour l'audit automatique des modifications d'entités.
 * 
 * Enregistre automatiquement toutes les opérations CRUD sur les entités :
 * - CREATE (INSERT)
 * - UPDATE (UPDATE)
 * - DELETE (DELETE)
 * 
 * Les modifications sont enregistrées de manière asynchrone pour ne pas
 * impacter les performances des transactions.
 *
 * @author AGBOKOUDJO Franck
 * @package App\Infrastructure\EventListener
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
                'entity_class' => (new \ReflectionClass($entity))->getShortName(),
                'entity_id' => $entityId,
                'changes' => $changes,
                'description' => $this->generateDescription($entity, $action, $entityId), // Description lisible
            ]
        );

        $this->enqueueActivity->dispatch(ActivityLogCommandHandler::class,'handle',[$activityLogCommand]);
    }

    /**
     * Nettoie et uniformise les données de changement.
     * 
     * ✅ Retourne TOUJOURS : ['old' => ..., 'new' => ...]
     */
    private function sanitizeChanges(array $data, ActivityAction $action): array
    {
        $sanitized = [];

        foreach ($data as $field => $values) {
            // Filtrer les champs sensibles
            if ($this->isSensitiveField($field)) {
                $sanitized[$field] = [
                    'old' => '***FILTERED***',
                    'new' => '***FILTERED***'
                ];
                continue;
            }

            // ✅ FORMAT UNIFORME pour tous les types d'actions
            $sanitized[$field] = match ($action) {
                ActivityAction::ENTITY_UPDATE => [
                    'old' => $this->formatValue($values[0] ?? null),
                    'new' => $this->formatValue($values[1] ?? null),
                ],
                ActivityAction::ENTITY_CREATE => [
                    'old' => null,
                    'new' => $this->formatValue($values),
                ],
                ActivityAction::ENTITY_DELETE => [
                    'old' => $this->formatValue($values),
                    'new' => null,
                ],
                default => [
                    'old' => null,
                    'new' => $this->formatValue($values),
                ],
            };
        }

        return $sanitized;
    }

     /**
     * Vérifie si un champ est sensible et doit être filtré.
     */
    private function isSensitiveField(string $field): bool
    {
        $sensitiveKeywords = ['password', 'token', 'secret', 'apikey', 'privatekey'];
        $fieldLower = strtolower($field);

        foreach ($sensitiveKeywords as $keyword) {
            if (str_contains($fieldLower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Formate une valeur pour l'enregistrement.
     */
    private function formatValue(mixed $value): mixed
    {
        // Null
        if ($value === null) {
            return null;
        }

        // Booléens
        if (is_bool($value)) {
            return $value;
        }

        // Objets
        if (is_object($value)) {
            // Collections Doctrine
            if ($value instanceof \Doctrine\Common\Collections\Collection) {
                return sprintf('[Collection: %d élément(s)]', $value->count());
            }

            // DateTime
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d H:i:s');
            }

            // Entités avec __toString()
            if (method_exists($value, '__toString')) {
                try {
                    return (string) $value;
                } catch (\Exception) {
                    // Fallback si __toString() échoue
                }
            }

            // Entités avec getId()
            if (method_exists($value, 'getId')) {
                return sprintf('[%s #%s]', 
                    (new \ReflectionClass($value))->getShortName(),
                    $value->getId()
                );
            }

            // Autres objets
            return sprintf('[Objet: %s]', get_class($value));
        }

        // Tableaux
        if (is_array($value)) {
            return sprintf('[Array: %d élément(s)]', count($value));
        }

        // Valeurs scalaires (string, int, float)
        // Tronquer les strings très longues
        if (is_string($value) && strlen($value) > 500) {
            return substr($value, 0, 500) . '... [tronqué]';
        }

        return $value;
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
