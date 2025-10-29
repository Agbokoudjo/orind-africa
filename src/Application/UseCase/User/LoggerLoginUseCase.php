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

use App\Domain\User\Model\LoggerLoginUser;
use App\Domain\User\Model\BaseUserInterface;
use App\Domain\User\Manager\LoggerLoginUserManagerInterface;
use App\Domain\User\Service\Registry\UserManagerRegistryInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class LoggerLoginUseCase
{
    public function __construct(
        private  UserManagerRegistryInterface $userManagerRegistry,
        private LoggerLoginUserManagerInterface $loggerLoginManager) {}
        
    public function recordLogin(BaseUserInterface $user,string $lastLoginIp):void{

        $userManager = $this->userManagerRegistry->getByUser($user);
        $lastLogin= new \DateTime('now', new \DateTimeZone('UTC'));
        $user->setLastLogin($lastLogin);
        $userManager->save($user);

        $loggerLogin = LoggerLoginUser::fromUser($user, $user->getRolePrincipal(),$lastLoginIp);
        $this->loggerLoginManager->doSave($loggerLogin);
    }
}
