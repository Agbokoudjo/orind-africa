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

namespace App\Infrastructure\Doctrine\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use App\Domain\User\MemberUserInterface;
use App\Domain\Action\GroupActionInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Infrastructure\Doctrine\Entity\User\SonataUser;
use App\Infrastructure\Doctrine\Entity\Action\GroupActionEntity;
use App\Infrastructure\Doctrine\Entity\User\Repository\MemberUserRepository;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[ORM\Entity(repositoryClass: MemberUserRepository::class)]
#[ORM\Table(name: "sonata_member_user")]
final class MemberUser extends SonataUser implements MemberUserInterface{

    public const ROLE_DEFAULT = 'ROLE_MEMBER';
    
    #[ORM\Column(type: "json", nullable: true)]
    protected ?array $skills = [];

    #[ORM\Column(type: "json", nullable: true)]
    protected ?array $interests = [];

    /**
     * @var iterable<int,GroupActionInterface>
     */
    #[ORM\ManyToMany(
        targetEntity: GroupActionEntity::class,
        inversedBy: 'members',
        cascade: ['persist'] 
    )]
    protected ?Collection $groups = null;

    public function __construct()
    {
        $this->groups=new ArrayCollection();
    }
    public function getRolePrincipal(): string{return self::ROLE_DEFAULT;}

    /**
     * Get the value of skills
     */
    public function getSkills(): ?array
    {
        return $this->skills;
    }

    /**
     * Set the value of skills
     */
    public function setSkills(?array $skills): void
    {
        $this->skills = $skills;
    }

    /**
     * Get the value of interests
     */
    public function getInterests(): ?array
    {
        return $this->interests;
    }

    /**
     * Set the value of interests
     */
    public function setInterests(?array $interests): void
    {
        $this->interests = $interests;
    }

    public function getGroups(): ?Collection
    {
        return $this->groups;
    }

    public function addGroup(GroupActionInterface $group):void
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addMember($this);
        }
    }

    public function removeGroup(GroupActionInterface $group):void
    {
        if($this->groups->removeElement($group)){
            $group->removeMember($this);
        }
    } 
}
