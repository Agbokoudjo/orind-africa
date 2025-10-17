<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Handler;

use Symfony\Bundle\SecurityBundle\Security;
use Sonata\AdminBundle\Admin\AdminInterface;
use App\Infrastructure\Security\Voter\AdminUserVoter;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AdminUserRoleSecurityHandler extends AbstractRoleSecurityHandler implements SecurityHandlerInterface
{
    public function __construct(private Security $security, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($authorizationChecker);
    }

    public function isGranted(AdminInterface $admin, string $attribute, ?object $object = null): bool
    {
        if ($object instanceof AdminInterface
            || (\is_string($attribute) && str_starts_with($attribute, 'ROLE_'))) {
            return $this->isGrantedRoleHierarchy($admin, $attribute, $object);
        }

        /**
         * @var AdminUserVoter
         */
        $perm = \constant(AdminUserVoter::class.'::PERMISSION_' . $attribute);
        return $this->security->isGranted($perm, $object);
    }
}
