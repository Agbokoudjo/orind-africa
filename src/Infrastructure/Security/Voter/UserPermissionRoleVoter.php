<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Voter;

use App\Domain\User\AdminUserInterface;
use App\Domain\Security\ObjectPermissionInterface;
use App\Domain\Security\UserPermissionRoleInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class UserPermissionRoleVoter extends Voter implements ObjectPermissionInterface
{

    public function __construct(private readonly AuthorizationCheckerInterface $authorizationCheckerForUser)
    {}
    
    protected function supports(string $attribute, mixed $subject): bool{

        return $subject instanceof UserPermissionRoleInterface ;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool{

        $_admin_user=$token->getUser();

        if(!$_admin_user instanceof AdminUserInterface){

            return false ;
        }


        $role_permission_role = \sprintf('ROLE_SONATA_ADMIN_PERMISSION_ROLE_%s', $attribute);
        return $this->authorizationCheckerForUser->isGranted($role_permission_role, $subject);
    }
}
