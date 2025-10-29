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
use App\Domain\Security\ObjectPermissionInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Application\Service\Authorization\AuthorizationCheckerForUserInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */

final class AdminUserVoter extends Voter implements ObjectPermissionInterface
{

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly ParameterBagInterface $services,
        private readonly AuthorizationCheckerForUserInterface $authorizationCheckerForUser)
    {}
    
    protected function supports(string $attribute, mixed $subject): bool
    {

        return $subject instanceof AdminUserInterface;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {

        $admin_user=$token->getUser();

        if(!$admin_user instanceof AdminUserInterface || !$subject instanceof AdminUserInterface){

            return false ;
        }
        
        // si le utilisateur n'as pas le role admin on return false ,immediatement ,seul les admin ont le droit
        // d'acceder a la liste des admin
        if (!$this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return false;
        }

        if ($admin_user->isSuperAdmin() || $admin_user->isFounder()) {
            return true;
        }

        if($admin_user->isMinister()){

            return $attribute ===self::PERMISSION_VIEW || $attribute === self::PERMISSION_EDIT ;
        }

        //pour les autres 
        return $this->authorizationCheckerForUser->isGrantedForUser($admin_user, $attribute);
    }

   
}
