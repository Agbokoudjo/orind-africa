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

namespace App\Application\Service\Authorization;

use App\Domain\User\Model\BaseUserInterface;
use App\Domain\User\Service\Resolver\UserTypeResolver;
use App\Domain\Security\UserPermissionRoleRepositoryInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class AuthorizationCheckerForUser implements AuthorizationCheckerForUserInterface
{
    public function __construct(private readonly UserPermissionRoleRepositoryInterface $userPermissionRoleRepo)
    {
        
    }

    public function isGrantedForUser(BaseUserInterface $user,string $role):bool{
        
        return $user->hasRole($role) 
            || $this->userPermissionRoleRepo->userHasRole(
           UserTypeResolver::resolveFromUser($user),
            $user->getId(),
            $role
        );
    }   
}
