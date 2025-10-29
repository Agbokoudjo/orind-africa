<?php declare(strict_types=1);

namespace App\Infrastructure\Security\Handler;

use App\Application\Service\Authorization\AuthorizationCheckerForUserInterface;
use App\Infrastructure\Security\Voter\LoggerLoginVoter;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class LoggerLoginSecurityHandler extends AbstractRoleSecurityHandler implements SecurityHandlerInterface
{
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($authorizationChecker);
    }

    public function isGranted(AdminInterface $admin, string $attribute, ?object $object = null): bool
    {
        if (
            $object instanceof AdminInterface ||
            (\is_string($attribute) && str_starts_with($attribute, 'ROLE_SONATA_ADMIN_LOGGER_'))
        ) {
            return $this->isGrantedRoleHierarchy($admin, $attribute, $object);
        }

        return true;
    }
}
