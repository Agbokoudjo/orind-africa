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
use App\Application\UseCase\Command\UserCommand;
use App\Domain\User\Manager\UserManagerInterface;
use App\Domain\User\Manager\UserManagerRegistryInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class AdminCreateUserCommandHandler
{
    public function __construct(private UserManagerRegistryInterface $userManagerRegistry){}
    public function handler(string $fqcnEntity,UserCommand $userDto):void{
        try {
            /**
             * @var UserManagerInterface
             */
            $domainUserManager = $this->userManagerRegistry->get($fqcnEntity);
        } catch (\RuntimeException $noFoundRegistry) {
            throw $noFoundRegistry;
        }
        /**
         * @var BaseUserInterface
         */
        $user=$domainUserManager->create();
        $user->setUsername($userDto->username);
        $user->setEmail($userDto->email);
        $user->setPlainPassword($userDto->password);
        $user->setEnabled($userDto->enabled);
        $domainUserManager->save($user);
    }
}
