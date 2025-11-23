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

namespace App\Infrastructure\Security\Handler;

use App\Infrastructure\Security\Voter\LoggerLoginVoter;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class LoggerLoginSecurityHandler extends AbstractRoleSecurityHandler 
{    public function __construct(
        private readonly Security $security,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($authorizationChecker);
    }

    public function isGranted(AdminInterface $admin, $attribute, ?object $object = null): bool
    {
        if (
            $object instanceof AdminInterface ||
            (\is_string($attribute) && str_starts_with($attribute, 'ROLE_SONATA_ADMIN_'))
        ) {
            return $this->isGrantedRoleHierarchy($admin, $attribute, $object);
        }

        return $this->security->isGranted(LoggerLoginVoter::PERMISSION_AUDITOR,$object);
    }
}
