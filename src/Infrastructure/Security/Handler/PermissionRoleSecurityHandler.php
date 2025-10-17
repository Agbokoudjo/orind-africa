<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Handler;

use Symfony\Bundle\SecurityBundle\Security;
use Sonata\AdminBundle\Admin\AdminInterface;
use App\Infrastructure\Security\Voter\PermissionRoleVoter;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class PermissionRoleSecurityHandler extends AbstractRoleSecurityHandler implements SecurityHandlerInterface
{
    public function __construct(private readonly Security $security,
                                private readonly AuthorizationCheckerInterface $authorizationChecker) {
        parent::__construct($authorizationChecker);
    }

    public function isGranted(AdminInterface $admin, string $attribute, ?object $object = null): bool
    {
        if($object instanceof AdminInterface){
            return $this->isGrantedRoleHierarchy($admin, $attribute, $object) ;
        }

        /**
         * @var PermissionRoleVoter
         */
        $perm = constant(PermissionRoleVoter::class . '::PERMISSION_' . $attribute);
        return $this->security->isGranted($perm, $object);
        
    }
   
}
