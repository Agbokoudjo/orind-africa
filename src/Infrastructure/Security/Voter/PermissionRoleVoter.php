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

namespace App\Infrastructure\Security\Voter;

use App\Domain\User\Model\AdminUserInterface;
use App\Domain\Security\PermissionRoleInterface;
use App\Domain\Security\ObjectPermissionInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class PermissionRoleVoter extends Voter implements ObjectPermissionInterface
{
    
    public function __construct(private readonly AuthorizationCheckerInterface $authorizationCheckerForUser){

    }
    protected function supports(string $attribute, mixed $subject): bool
    {   

        return $subject instanceof PermissionRoleInterface ;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool{

        $admin_user=$token->getUser();
        
        if(!$admin_user instanceof AdminUserInterface){
            return false;
        }
        
        if(!$subject instanceof PermissionRoleInterface){
            return false ;
        }

        if($attribute === self::PERMISSION_DELETE || $attribute === self::PERMISSION_EDIT){
            /**
             * On verfier si l'utilisateur connecter actuelle est l'utilisateur qui a creer l'object
             */
            return $admin_user->getId() === $subject->getCreatedBy()->getId();
        }

        if($admin_user->isSuperAdmin() || $admin_user->isFounder()){

            return true ;
        }

        $role_permission_role=$attribute ;
        if(\is_string($attribute) && !str_starts_with($attribute, 'ROLE_')){

            $role_permission_role = \sprintf('ROLE_SONATA_ADMIN_PERMISSION_ROLE_%s', $attribute);
        }
       
        return $this->authorizationCheckerForUser->isGranted($role_permission_role, $subject) ;
    }
}
