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

namespace App\Application\UseCase\CommandHandler;

use App\Domain\User\BaseUserInterface;
use App\Domain\User\Event\PromoteUserEvent;
use App\Application\Service\EventBusInterface;
use App\Domain\User\Manager\UserManagerInterface;
use App\Application\UseCase\Command\PromoteUserCommand;
use App\Domain\User\Manager\UserManagerRegistryInterface;
use App\Domain\User\Exception\DomainUserNotFoundException;
/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class PromoteUserCommandHandler
{
    public function __construct(
        private UserManagerRegistryInterface $userManagerRegistry,
        private EventBusInterface $eventBus) {}

    /**
     * @param string $username
     * @param PromoteUserCommand $promote_user
     * @throws DomainUserNotFoundException  if username no exist the database
     * @return void
     */
    public function handler(string $fqcnEntity ,PromoteUserCommand $promote_user): void
    {
        try {
            /**
             * @var UserManagerInterface
             */
            $domainUserManager = $this->userManagerRegistry->get($fqcnEntity);
        } catch (\RuntimeException $noFoundRegistry) {
            throw $noFoundRegistry;
        }
        /**
         * @var BaseUserInterface|null
         */
        $user = $domainUserManager->findUserByUsernameOrEmail($promote_user->username);

        if (null === $user) {
            throw new DomainUserNotFoundException($promote_user->username);
        }
        
        /**
         * La fonction addRole de BaseUserInterface verifie en intern si le role passer en parametre
         * l'inclusion du role dans les roles existant de User 
         */
        foreach ($promote_user->roles as $role) {
            $user->addRole($role);
        }
        $domainUserManager->save($user);

        /**
         * on dispatch un evenement de type PromoteUserEvent et les different Listener ou Subscriber vont se greffer sur ça
         */
        $this->eventBus->dispatch(new PromoteUserEvent($user,$promote_user->roles));
    }
}
