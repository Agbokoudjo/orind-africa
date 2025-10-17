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
use App\Domain\User\AdminUserInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Action\DomainActionMinisterInterface;
use App\Infrastructure\Doctrine\Entity\User\SonataUser;
use App\Infrastructure\Doctrine\Entity\Action\DomainActionMinisterEntity;
use App\Infrastructure\Doctrine\Entity\User\Repository\AdminUserRepository;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[ORM\Entity(repositoryClass: AdminUserRepository::class)]
#[ORM\Table(name: "sonata_admin_user")]
final class AdminUser extends SonataUser implements AdminUserInterface
{
    public const ROLE_DEFAULT = 'ROLE_ADMIN';

    /**
     * One product has many features. This is the inverse side.
     * @var Collection<int, DomainActionMinisterInterface>
     */
    #[ORM\OneToMany(targetEntity:DomainActionMinisterEntity::class,cascade:['persist'],mappedBy: "owner",orphanRemoval:false)]
    public ?Collection $domains;

    public function __construct(){
        
        $this->domains=new ArrayCollection();
    }
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    public function setSuperAdmin(bool $boolean): void
    {
        if (true === $boolean) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }
    }
    public function isFounder(): bool
    {
        return $this->hasRole('ROLE_FOUNDER');
    }

    public function isCoFounder(): bool
    {
        return $this->hasRole('ROLE_COFOUNDER');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('ROLE_ADMIN');
    }

    public function getRolePrincipal(): string
    {
        if($this->isFounder()){return 'ROLE_FOUNDER';}

        if($this->isCoFounder()){return 'ROLE_COFOUNDER';}

        if($this->isSuperAdmin()){return self::ROLE_SUPER_ADMIN;}
        
        if($this->isMinister()){ return 'ROLE_MINISTER' ;}
        
        return self::ROLE_DEFAULT;
    }

    public function isMinister():bool{

      return $this->hasRole('ROLE_MINISTER');
    }
   

    public function getDomains():?Collection{
        return $this->domains ;
    }

    public function addDomains(DomainActionMinisterInterface $domainAction):void{

        if (!$this->domains->contains($domainAction)) {
            $this->domains[] = $domainAction;
            $domainAction->setOwner($this);
        }
    }
}