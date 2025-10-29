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
use App\Domain\User\Model\MemberUserInterface;
use App\Domain\Security\ObjectPermissionInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Infrastructure\Doctrine\Entity\User\LoggerLoginUserEntity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Application\Service\Authorization\AuthorizationCheckerForUserInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class ActivityLogVoter extends Voter implements ObjectPermissionInterface
{
    public const PERMISSION_AUDITOR = "ROLE_AUDITOR_ACTIVITY_LOG";

    public const SUPPORT = [
        self::PERMISSION_AUDITOR,
        self::PERMISSION_LIST,
        self::PERMISSION_EXPORT,
        self::PERMISSION_VIEW
    ];

    public function __construct(
        private readonly AuthorizationCheckerForUserInterface $authorizationCheckerForUser
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, self::SUPPORT)
            && $subject instanceof LoggerLoginUserEntity;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {

        $_user = $token->getUser();

        if (!$_user instanceof BaseUserInterface || !$subject instanceof MemberUserInterface) {

            return false;
        }

        return $this->authorizationCheckerForUser->isGrantedForUser($_user, $attribute);
    }
}
