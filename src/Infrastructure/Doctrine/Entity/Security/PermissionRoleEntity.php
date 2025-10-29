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

namespace App\Infrastructure\Doctrine\Entity\Security;

use Doctrine\ORM\Mapping as ORM;
use App\Domain\Security\PermissionRole;
use App\Domain\User\Model\AdminUserInterface;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use App\Infrastructure\Doctrine\Entity\Security\Repository\PermissionRoleRepository;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[ORM\Entity(repositoryClass: PermissionRoleRepository::class)]
#[ORM\Table(name: "permission_role")]
final class PermissionRoleEntity extends PermissionRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'integer')]
    protected int|string|null $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    protected ?string $name = null;

    #[ORM\Column(type: 'text')]
    protected ?string $description = null;

    #[ORM\Column(type: 'string', length: 200)]
    protected ?string $context= null;

    #[ORM\ManyToOne(targetEntity: AdminUser::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected ?AdminUserInterface $createdBy = null;

    #[ORM\Column(type: "datetime_immutable")]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTimeInterface $updatedAt = null;

}
