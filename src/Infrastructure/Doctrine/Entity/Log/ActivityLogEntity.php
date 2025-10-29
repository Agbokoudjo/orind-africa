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
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Log\Enum\ActivityAction;
use App\Infrastructure\Doctrine\Entity\Log\ActivityLogRepository;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[ORM\Entity(repositoryClass: ActivityLogRepository::class)]
#[ORM\Table(name: 'activity_log')]
#[ORM\Index(fields: ['createdAt'])]
#[ORM\Index(fields: ['ipAddress'])]
class ActivityLogEntity extends ActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue('IDENTITY')]
    #[ORM\Column(type: "integer")]
    protected int|string|null $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    protected ?\DateTimeInterface $createdAt = null;

    // Laissez nullable car les actions anonymes existent
    #[ORM\Column(type: "json", options: ['jsonb' => true])]
    protected readonly array $userContext;

    #[ORM\Column(length: 45)]
    protected readonly string $ipAddress;

    #[ORM\Column(type:"string",length:100,enumType:ActivityAction::class)]
    protected  readonly ActivityAction $action;

    #[ORM\Column(length:20)]
    protected readonly string $method;

    #[ORM\Column(length:255)]
    protected readonly string $route;
    // Données détaillées (User-Agent, entité modifiée, etc.).
    #[ORM\Column(type: "json", options: ['jsonb' => true])] 
    protected readonly array $context;
}