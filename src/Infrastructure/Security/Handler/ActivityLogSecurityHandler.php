<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Handler;

use Symfony\Bundle\SecurityBundle\Security;
use Sonata\AdminBundle\Admin\AdminInterface;
use App\Infrastructure\Security\Voter\LoggerLoginVoter;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ActivityLogSecurityHandler extends AbstractRoleSecurityHandler implements SecurityHandlerInterface
{
    public function __construct(private Security $security, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($authorizationChecker);
    }

    public function isGranted(AdminInterface $admin, string $attribute, ?object $object = null): bool
    {
        if (
            $object instanceof AdminInterface
            || (\is_string($attribute) && str_starts_with($attribute, 'ROLE_'))
        ) {
            return $this->isGrantedRoleHierarchy($admin, $attribute, $object);
        }

        /**
         * @var LoggerLoginVoter
         */
        $perm = \constant(LoggerLoginVoter::class . '::PERMISSION_' . $attribute);
        return $this->security->isGranted($perm, $object);
    }
}
