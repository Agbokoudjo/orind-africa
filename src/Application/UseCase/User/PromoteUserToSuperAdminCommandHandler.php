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

namespace App\Application\UseCase\User;

use App\Domain\User\Enum\UserType;
use App\Application\Bus\EventBusInterface;
use App\Domain\User\Model\AdminUserInterface;
use App\Application\Service\TranslatorInterface;
use App\Domain\User\Manager\UserManagerInterface;
use App\Domain\User\Event\PromoteUserToSuperAdminEvent;
use App\Domain\User\Exception\DomainUserNotFoundException;
use App\Domain\User\Message\PromoteUserToSuperAdminCommand;
use App\Domain\User\Service\Registry\UserManagerRegistryInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class PromoteUserToSuperAdminCommandHandler
{
    public function __construct(
        private UserManagerRegistryInterface $userManagerRegistry,
        private EventBusInterface $eventBus,
        private TranslatorInterface $translator
    ) {}

    /**
     * @param PromoteUserToSuperAdminCommand $promote_user
     * @throws DomainUserNotFoundException  if username no exist the database
     * @return void
     */
    public function handle(UserType $userType, PromoteUserToSuperAdminCommand $promote_user): void
    {
        try {
            /**
             * @var UserManagerInterface
             */
            $domainUserManager = $this->userManagerRegistry->getByUserType($userType);
        } catch (\RuntimeException $noFoundRegistry) {
            throw $noFoundRegistry;
        }
        
        /**
         * @var AdminUserInterface|null
         */
        $user = $domainUserManager->findUserByUsernameOrEmail($promote_user->username);

        if (null === $user) {
            throw new DomainUserNotFoundException($promote_user->username);
        }

        if($user->isSuperAdmin()){
            throw new \InvalidArgumentException(
                $this->translator->trans('user.already_super_admin', ['%username%' => $promote_user->username], 'exception')
            );
        }

        $user->setSuperAdmin(true);
        $domainUserManager->save($user);

        /**
         * on dispatch un evenement de type PromoteUserToSuperAdminEvent et les different Listener ou Subscriber vont se greffer sur ça
         */
        $this->eventBus->dispatch(new PromoteUserToSuperAdminEvent($user));
    }
}
