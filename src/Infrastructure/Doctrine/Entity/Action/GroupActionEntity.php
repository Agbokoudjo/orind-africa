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

use Doctrine\ORM\Mapping as ORM;
use App\Domain\Action\GroupAction;
use Doctrine\Common\Collections\Collection;
use App\Domain\User\Model\AdminUserInterface;
use App\Domain\User\Model\MemberUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Action\DomainActionMinisterInterface;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use App\Infrastructure\Doctrine\Entity\User\MemberUser;
use App\Infrastructure\Doctrine\Entity\Action\DomainActionMinisterEntity;
use App\Infrastructure\Doctrine\Entity\Action\Repository\GroupActionRepository;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 *
 */
#[ORM\Entity(repositoryClass:GroupActionRepository::class)]
#[ORM\Table(name: "group_action")]
final class GroupActionEntity  extends GroupAction
{
    #[ORM\Id]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'integer')]
    protected int|string|null $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected ?string $name = null;

    #[ORM\Column(type: 'text')]
    protected ?string $description = null;

    #[ORM\ManyToOne(
        targetEntity: DomainActionMinisterEntity::class,  
        cascade: ["persist"],
        inversedBy: "groups")
    ]
    #[ORM\JoinColumn(nullable:true, onDelete: "SET NULL")]
    protected readonly  ?DomainActionMinisterInterface $domain;

    #[ORM\ManyToOne(
            targetEntity: AdminUser::class,
            cascade: ["persist"],
        )
    ]
    #[ORM\JoinColumn(nullable: false, onDelete: "SET NULL")]
    protected ?AdminUserInterface $currentManagerGroup = null;
    
    #[ORM\Column(type: "string")]
    protected readonly  ?string $createdByUsername;

    /**
     * @var Collection<int,MemberUserInterface>
     */
    // Dans l'entité GroupActionEntity

    /**
     * @var Collection<int,MemberUserInterface>
     */
    #[ORM\ManyToMany(
        targetEntity: MemberUser::class,
        mappedBy: 'groups',
        cascade: ['persist'],
        orphanRemoval: false
    )]
    protected $members;

    public function __construct()
    {
        $this->members=new ArrayCollection(); 
    }

    public function getMembers(): ?Collection
    {
        return $this->members ;
    }
    
    public function addMember(MemberUserInterface $memberUser): void {
        
        if(!$this->members->contains($memberUser)){

            $this->members->add($memberUser);
        }
    }

    public function removeMember(MemberUserInterface $memberUser): void {

        $this->members->removeElement($memberUser);
    }
}
