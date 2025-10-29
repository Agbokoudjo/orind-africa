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

namespace App\Infrastructure\Doctrine\Entity\Action;

use App\Domain\Action\Domain;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Domain\User\Model\AdminUserInterface;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use App\Infrastructure\Doctrine\Entity\Action\GroupActionEntity;
use App\Infrastructure\Doctrine\Entity\Action\Repository\DomainActionMinisterRepository;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 *
 */
#[ORM\Entity(repositoryClass:DomainActionMinisterRepository::class)]
#[ORM\Table(name: "domain_action")]
final class DomainActionMinisterEntity  extends Domain implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'integer')]
    protected int|string|null $id=null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected ?string $name=null;

    #[ORM\Column(type: 'text')] 
    protected ?string $description=null;

    #[ORM\ManyToOne( 
        targetEntity: AdminUser::class,
        inversedBy: "domains",
        cascade: ["persist"]
    )]
    #[ORM\JoinColumn(
        name: "owner_id",
        referencedColumnName: "id",
        nullable: true,
        onDelete: "SET NULL"
    )]
    protected ?AdminUserInterface $owner=null;

    /**
     *
     * @var Collection<int,GroupActionInterface>|null
     */
    #[ORM\OneToMany(targetEntity:GroupActionEntity::class,mappedBy: "domain",orphanRemoval:false)]
    protected ?Collection $groups;

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getGroups(): ?Collection
    {
        return $this->groups;
    }
}

