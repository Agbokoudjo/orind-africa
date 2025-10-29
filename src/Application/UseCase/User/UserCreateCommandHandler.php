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

use App\Domain\User\BaseUserInterface;
use App\Domain\User\Enum\UserType;
use App\Domain\User\Message\UserCreateCommand;
use App\Domain\User\Manager\UserManagerInterface;
use App\Domain\User\Service\Registry\UserManagerRegistryInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class UserCreateCommandHandler
{
    public function __construct(private UserManagerRegistryInterface $userManagerRegistry){}
    
    public function handle(UserType $userType,UserCreateCommand $userDto):void{
        try {
            /**
             * @var UserManagerInterface
             */
            $domainUserManager = $this->userManagerRegistry->getByUserType($userType);
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
