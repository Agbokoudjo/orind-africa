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

namespace App\Infrastructure\Listener\User;

use App\Application\Queue\AsyncMethodDispatcherInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use App\Domain\User\Model\BaseUserInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use App\Application\Queue\EnqueueMethodInterface;
use App\Domain\User\Message\UpdateUserProfileCommand;
use App\Domain\User\Service\Resolver\UserTypeResolver;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use App\Application\UseCase\User\UpdateUserProfileCommandHandler;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final readonly class UserListener 
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private AsyncMethodDispatcherInterface $enqueueMethod
        ) {}

    #[AsDoctrineListener(event: Events::prePersist, priority: 500)]
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof BaseUserInterface || $entity->getId() !==null) return;
        
        $entity->prePersist();
    }

    #[AsDoctrineListener(event: Events::preUpdate, priority: 500)]
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof BaseUserInterface || $entity->getId() === null) return;

        $entity->preUpdate();
    }

    #[AsDoctrineListener(event: Events::postPersist, priority: 500)]
    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof BaseUserInterface || $entity->getId() === null) return;

        $this->dispatchUpdateCommand($entity);
    }

    #[AsDoctrineListener(event: Events::postUpdate, priority: 0)]
    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof BaseUserInterface || $entity->getId() === null) return;

        $this->dispatchUpdateCommand($entity);
    }

    private function dispatchUpdateCommand(BaseUserInterface $entity): void
    {
        // 1. Dispatcher le message
        $command = new UpdateUserProfileCommand(
            $entity->getId(),
           UserTypeResolver::resolveFromUser($entity)
        );

        // 2. Envoyer le message au bus asynchrone
        $this->enqueueMethod->dispatch(UpdateUserProfileCommandHandler::class,'handle',[$command]);
    }
}
