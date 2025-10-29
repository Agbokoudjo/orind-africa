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
use App\Domain\Action\DomainActionMinisterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Infrastructure\Doctrine\Entity\Action\DomainActionMinisterEntity;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class DomainActionVoter extends Voter implements ObjectPermissionInterface
{

    public function __construct(private readonly AuthorizationCheckerInterface $authorizationCheckerForUser) {}
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof DomainActionMinisterInterface ;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $adminUser = $token->getUser();

        if (!$adminUser instanceof AdminUserInterface) {
            return false;
        }

        // ✅ Si SuperAdmin ou Fondateur → accès complet
        if ($adminUser->isSuperAdmin() || $adminUser->isFounder()) {
            return true;
        }

        // ✅ CoFondateur,✅ Ministre → accès lecture seule → peuvent voir mais pas créer/éditer/supprimer
        if ($adminUser->isCoFounder() || $adminUser->isMinister()) {
            return $attribute === self::PERMISSION_VIEW || self::PERMISSION_LIST;
        }

        $role_permission_role = \sprintf('ROLE_SONATA_ADMIN_DOMAIN_ACTION_%s', $attribute);
        return $this->authorizationCheckerForUser->isGranted($role_permission_role, $subject);
    }


    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, DomainActionMinisterEntity::class, true);
    }
}
