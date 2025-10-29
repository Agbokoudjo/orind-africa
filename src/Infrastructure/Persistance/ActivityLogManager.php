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

use App\Domain\Log\Manager\ActivityLogManagerInterface;
use App\Infrastructure\Doctrine\Entity\Log\ActivityLogEntity;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\Doctrine\Entity\BaseEntityManager;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class ActivityLogManager extends BaseEntityManager implements ActivityLogManagerInterface
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct(ActivityLogEntity::class, $registry);
    }
}
