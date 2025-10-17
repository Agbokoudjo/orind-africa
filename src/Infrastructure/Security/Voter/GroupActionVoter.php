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

use App\Domain\User\AdminUserInterface;
use App\Domain\Action\GroupActionInterface;
use App\Domain\Security\ObjectPermissionInterface;
use App\Domain\Action\DomainActionMinisterInterface;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Infrastructure\Doctrine\Entity\Action\GroupActionEntity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class GroupActionVoter extends Voter implements ObjectPermissionInterface
{
    public function __construct(private readonly AuthorizationCheckerInterface $authorizationCheckerForUser) {}
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof GroupActionInterface;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $adminUser = $token->getUser();

        if(!$subject instanceof GroupActionInterface){
            return false ;
        }

        if (!$adminUser instanceof AdminUserInterface) {
            return false;
        }

        if($attribute === self::PERMISSION_VIEW){
            return $this->canView($adminUser,$subject);
        }

        if (in_array($attribute, [
            self::PERMISSION_EDIT, 
            self::PERMISSION_DELETE, 
            self::PERMISSION_CREATE], true)) {

            return $this->canEditorOrDelete($attribute,$adminUser,$subject);
        }

        $role_permission_role = \sprintf('ROLE_SONATA_ADMIN_GROUP_ACTION_%s', $attribute);
        return $this->authorizationCheckerForUser->isGranted($role_permission_role, $subject);
    }
    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, GroupActionEntity::class, true);
    }

    private function canView(
        AdminUserInterface $user,
        GroupActionInterface $subject
        ):bool{
        
        /**
         * selon l'achitecture de l'entity AdminUser les admins incluent
         * (superAdmin,fondateur,co-fonateurs et les ministres )
         * si un admin n'est pas n'est pas un ministre donc il es dans (superAdmin,fondateur,co-fondateur)
         */
        if(!$user->isMinister()){
            return true;
        }
        /**
         * sinon ,c'est surement un ministre vu de l'achitecture,
         * on verifie si celui qui veut consulter le groupe 
         */
        return $subject->getCurrentManagerGroup()->getId() === $user->getId() ;
    }

    private function canEditorOrDelete(
        string $attribute,
        AdminUser $adminUser, 
        GroupActionInterface $groupAction):bool{

        if(!$adminUser->isMinister()){
            return false ;
        }

        // ✅ Vérification que le ministre est bien lié au domaine du groupe
        $hasDomainAccess = $adminUser->getDomains()->exists(
            fn($key, DomainActionMinisterInterface $domain) =>
            $domain->getId() === $groupAction->getDomain()->getId()
        );

        if (!$hasDomainAccess) {
            return false;
        }

        // ✅ Ministre → peut CREATE sans restriction
        if ($attribute === self::PERMISSION_CREATE) {
            return true;
        }

        // ✅ Ministre → EDIT / DELETE uniquement si manager du groupe
        if (in_array($attribute, [self::PERMISSION_EDIT, self::PERMISSION_DELETE], true)) {

            return $groupAction->getCurrentManagerGroup()?->getId() === $adminUser->getId();
        }

        return false ;
    }
}
