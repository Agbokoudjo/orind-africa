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

namespace App\Domain\Action;

use App\Domain\User\AdminUserInterface;
use App\Domain\User\MemberUserInterface;
use App\Domain\Action\DomainActionMinisterInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
abstract class GroupAction implements GroupActionInterface,\Stringable
{
    protected string|int|null $id = null;

    protected ?string $name = null;

    protected ?string $description = null;

    protected  ?\DateTimeInterface $createdAt = null;

    protected ?\DateTimeInterface $updatedAt = null;

    protected ?AdminUserInterface $currentManagerGroup = null;

    protected readonly ?string $createdByUsername ; // snapshot

    /** @var iterable<int,MemberUserInterface> */
    protected $members = [];

    protected readonly ?DomainActionMinisterInterface $domain;

    /**
     * Get the value of id
     */
    public function getId(): string|int|null
    {
        return $this->id;
    }


    /**
     * Get the value of name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the value of description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the value of description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * Get the value of createdAt
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get the value of updatedAt
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Set the value of updatedAt
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getCreatedByUsername(): ?string
    {
        return $this->createdByUsername;
    }

    public function setCreatedByUsername(string $username): void{

        $this->createdByUsername=$username ;
    }

    public function getDomain(): ?DomainActionMinisterInterface{

        return $this->domain;
    }

    public function setDomain(?DomainActionMinisterInterface $domain): void{

        $this->domain=$domain;
    }

    public function getCurrentManagerGroup(): ?AdminUserInterface{

        return $this->currentManagerGroup ;   
     }

    public function setCurrentManagerGroup(AdminUserInterface $adminUser): void{

        $this->currentManagerGroup=$adminUser;
    }

    /**
     * @return iterable<int,MemberUserInterface>
     */
    public function getMembers(): ?iterable{

        return  $this->members;
    }

    public function addMember(MemberUserInterface $memberuser): void{

    }

    public function removeMember(MemberUserInterface $memberuser): void{

    }
}
