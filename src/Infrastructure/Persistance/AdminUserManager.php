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

namespace App\Infrastructure\Persistance;

use Doctrine\Persistence\ManagerRegistry;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use App\Infrastructure\Persistance\AbstractUserManager;
use App\Domain\User\Service\CanonicalFieldsUpdaterInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AutoconfigureTag('app.user_manager')]
final class AdminUserManager extends AbstractUserManager
{
    public function __construct(
        ManagerRegistry $registry,
        CanonicalFieldsUpdaterInterface $canonicalFieldsUpdater,
        UserPasswordHasherInterface $userPasswordHasher,
    ) {
        parent::__construct(AdminUser::class,$registry, $canonicalFieldsUpdater, $userPasswordHasher);
    }

    public function getFQCN(): string {return AdminUser::class;}
}
