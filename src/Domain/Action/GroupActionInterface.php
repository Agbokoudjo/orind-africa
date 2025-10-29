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

use App\Domain\User\Model\AdminUserInterface;
use App\Domain\User\Model\MemberUserInterface;

interface GroupActionInterface
{
    public function getId(): string|int|null;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getDescription(): ?string;

    public function setDescription(?string $description): void;

    public function getCreatedAt(): ?\DateTimeInterface;

    public function setCreatedAt(?\DateTimeInterface $createdAt): void;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void;

    public function getDomain(): ?DomainActionMinisterInterface;

    public function setDomain(?DomainActionMinisterInterface $domain): void;

    /**
     * @return iterable<int,MemberUserInterface>
     */
    public function getMembers(): ?iterable;

    public function addMember(MemberUserInterface $memberuser):void;

    public function removeMember(MemberUserInterface $memberuser): void;

    public function getCreatedByUsername():?string;

    public function setCreatedByUsername(string $username):void;

    public function getCurrentManagerGroup(): ?AdminUserInterface;

    public function setCurrentManagerGroup(AdminUserInterface $adminUser):void;
}
