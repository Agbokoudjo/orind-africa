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

use App\Domain\User\Model\BaseUserInterface;
use App\Domain\User\Model\AdminUserInterface;
use App\Domain\User\Model\MemberUserInterface;
use App\Domain\Security\UserProfileEditPermissionsInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Application\Service\Authorization\AuthorizationCheckerForUserInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class UserProfileEditVoter extends Voter implements UserProfileEditPermissionsInterface
{
   
    public function __construct(
        private readonly AuthorizationCheckerForUserInterface $authorizationCheckerForUser
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute,self::PERMISSION_EDITOR_USER_PROFILE)
              && $subject instanceof BaseUserInterface ;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $_user=$token->getUser();
        
        if(!$subject instanceof BaseUserInterface || !$_user instanceof BaseUserInterface){

            return false ;
        }

        
        if($attribute === self::PERMISSION_CAN_EDIT_OWN_PROFILE){

            return $_user->getId() === $subject->getId() ;
        }

        //Les MemberUser n'ont pas le droit de modifier leur propre (username,email,phone)
        if($_user instanceof MemberUserInterface){

            return false ;
        }

        /**
         * Seul les administrateur(fondateur et certains possedant le role 'PERMISSION_EDITOR_USER_PROFILE') ont droit le droit de modifier les noms des utilisateurs 
         * du systeme
         */
        if(!$_user instanceof AdminUserInterface){

            return false ;
        }

        if($_user->isFounder()){
            return true ;
        }

        //meme les nouveaux superAdmin nommer n'ont pas droit a certains modification sauf le createur du backoffice
        return $this->authorizationCheckerForUser->isGrantedForUser($_user, 'PERMISSION_EDITOR_USER_PROFILE');
      
    }
}
