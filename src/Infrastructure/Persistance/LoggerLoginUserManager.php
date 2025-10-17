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

use App\Domain\User\LoggerLoginUser;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\Doctrine\Entity\BaseEntityManager;
use App\Domain\User\Manager\LoggerLoginUserManagerInterface;
use App\Domain\User\Service\CanonicalFieldsUpdaterInterface;
use App\Infrastructure\Doctrine\Entity\User\LoggerLoginUserEntity;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class LoggerLoginUserManager extends BaseEntityManager implements LoggerLoginUserManagerInterface
{

    public function __construct(
        ManagerRegistry $registry,
        private CanonicalFieldsUpdaterInterface $canonicalFieldsUpdater
    ) {
        parent::__construct(LoggerLoginUserEntity::class, $registry);
    }
    public function doSave(LoggerLoginUser $log): void
    {
        $entity = LoggerLoginUserEntity::fromDomain($log);
        parent::save($entity);
    }
}
