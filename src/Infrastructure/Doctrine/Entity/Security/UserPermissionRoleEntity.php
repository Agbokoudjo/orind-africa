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
use App\Domain\User\Enum\UserType;
use App\Domain\Security\UserPermissionRole;
use App\Domain\Security\PermissionRoleInterface;
use App\Infrastructure\Doctrine\Entity\Security\Repository\UserPermissionRoleRepository;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[ORM\Entity(repositoryClass: UserPermissionRoleRepository::class)]
#[ORM\Table(name: "user_permission_role")]
final class UserPermissionRoleEntity extends UserPermissionRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected int|string|null $id = null;

    #[ORM\Column(type: 'string', length: 50, enumType: UserType::class)]
    protected readonly UserType $userType;

    #[ORM\Column(type: 'integer')]
    protected readonly int|string $userId;

    #[ORM\ManyToOne(targetEntity: PermissionRoleEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    protected readonly PermissionRoleInterface $roles;

    #[ORM\Column(type: 'datetime_immutable')]
    protected readonly \DateTimeInterface $assignedAt;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected ?string $scope = null;
}