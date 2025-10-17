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

namespace App\Application\UseCase;

use App\Domain\User\BaseUserInterface;
use App\Domain\User\Enum\AccountStatus;
use App\Application\Service\EventBusInterface;
use App\Domain\User\Event\ToggleUserAccountEvent;
use App\Domain\User\Service\UserManagerInterface;
use App\Infrastructure\Service\UserManagerRegistry;
use App\Domain\User\Exception\DomainUserNotFoundException;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class ToggleUserAccountUseCase
{
    public function __construct(
        private UserManagerRegistry $user_manager_registry,
        private EventBusInterface $eventBus) {}

    /**
     * Undocumented function
     *
     * @param string $username
     * @param AccountStatus $status
     * @throws \DomainUserNotFoundException  if username no exist the database
     * @return void
     */
    public function handler(
        string $fqcnEntity,
        string $username, 
        AccountStatus $status = AccountStatus::ACTIVE): void
    {
        try {
            /**
             * @var UserManagerInterface
             */
            $domainUserManager = $this->user_manager_registry->get($fqcnEntity);
        } catch (\RuntimeException $noFoundRegistry) {
            throw $noFoundRegistry;
        }
        /**
         * @var BaseUserInterface|null
         */
        $user = $domainUserManager->findUserByUsernameOrEmail($username);

        if (null === $user) {
            throw new DomainUserNotFoundException($username);
        }
        $user->setEnabled($status->toBool());
        $domainUserManager->save($user);

        /** 
         * on declencher un evenement de type ToggleUserAccountEvent 
         * pour signaler que le compte utilisateur est activer donc les evenements listener vont
         * se greffer sur ça
        */
        $this->eventBus->dispatch(new ToggleUserAccountEvent($user));
    }
}
