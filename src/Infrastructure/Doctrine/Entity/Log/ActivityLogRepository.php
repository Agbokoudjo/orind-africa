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

namespace App\Infrastructure\Doctrine\Entity\Log;

use App\Domain\Log\ActivityLog;
use Doctrine\Persistence\ManagerRegistry;
use App\Domain\Log\ActivityLogRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
class ActivityLogRepository  extends ServiceEntityRepository implements ActivityLogRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry
    ) {
        parent::__construct($managerRegistry,ActivityLogEntity::class);
    }

    public function add(ActivityLog $log,$andFlush=true): void
    {
        $this->getEntityManager()->persist($log);

        if($andFlush){

            $this->getEntityManager()->flush() ;
        }
    }
}