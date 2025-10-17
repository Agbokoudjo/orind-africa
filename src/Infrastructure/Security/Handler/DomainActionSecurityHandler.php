<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Handler;

use App\Infrastructure\Security\Voter\DomainActionVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class DomainActionSecurityHandler extends AbstractRoleSecurityHandler implements SecurityHandlerInterface
{
    public function __construct(private readonly Security $security, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($authorizationChecker);
    }

    public function isGranted(AdminInterface $admin, string $attribute, ?object $object = null): bool
    {
        if ($object instanceof AdminInterface) {
            return $this->isGrantedRoleHierarchy($admin, $attribute, $object);
        }

        /**
         * @var DomainActionVoter
         */
        $perm = constant(DomainActionVoter::class . '::PERMISSION_' . $attribute);
        return $this->security->isGranted($perm, $object);
    }
}
