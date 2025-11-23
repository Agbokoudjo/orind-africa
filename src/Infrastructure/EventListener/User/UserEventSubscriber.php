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

namespace App\Infrastructure\EventListener\User;

use App\Domain\User\Model\BaseUserInterface;
use Sonata\AdminBundle\Event\PersistenceEvent;
use App\Domain\User\Message\UpdateUserProfileCommand;
use App\Domain\User\Service\Resolver\UserTypeResolver;
use App\Application\Queue\AsyncMethodDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Application\UseCase\User\UpdateUserProfileCommandHandler;

/**
 * Event Subscriber pour les événements Doctrine des utilisateurs
 * 
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final readonly class UserEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AsyncMethodDispatcherInterface $enqueueMethod
        ) {}

    
    public static function getSubscribedEvents(): array
    {
        return [
            'sonata.admin.event.persistence.post_update' => ['postUpdate',500],
            'sonata.admin.event.persistence.post_persist'=>['postPersit',500]
          
        ];
    }

    public function postPersit(PersistenceEvent $sonataEvent): void
    {
        $entity = $sonataEvent->getObject();
        if (!$entity instanceof BaseUserInterface) return;

        $this->dispatchUpdateCommand($entity);
    }


    final public function postUpdate(PersistenceEvent $sonataEvent): void
    {
        $entity=$sonataEvent->getObject();
        if (!$entity instanceof BaseUserInterface) return;

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
        $this->enqueueMethod->dispatch(UpdateUserProfileCommandHandler::class, 'handle', [$command]);
    }
}
