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

use App\Domain\User\BaseUserInterface;
use App\Domain\User\AdminUserInterface;
use App\Domain\User\MemberUserInterface;
use App\Domain\Security\ObjectPermissionInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Application\Service\Authorization\AuthorizationCheckerForUserInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */

final class MemberUserVoter extends Voter implements ObjectPermissionInterface
{
    public const PERMISSION_SKILLS="SKILLS";
    public const PERMISSION_INTERESTS= "INTERESTS";

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationCheckerForUser
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {

        return $subject instanceof MemberUserInterface;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {

        $_user = $token->getUser();

        if (!$_user instanceof BaseUserInterface || !$subject instanceof MemberUserInterface) {

            return false;
        }

        if($attribute === self::PERMISSION_SKILLS || $attribute === self::PERMISSION_INTERESTS){
            
            return $_user->getId() === $subject->getId();
        }

        //pour les autres 
        $_role = \sprintf('ROLE_SONATA_ADMIN_USER_MEMBER_%s', $attribute);
        return $this->authorizationCheckerForUser->isGranted($_role,$subject);
    }


   
}
