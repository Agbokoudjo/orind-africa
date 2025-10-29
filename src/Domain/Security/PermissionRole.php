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

namespace App\Domain\Security;

use App\Domain\ModelTrait\CreatedAtTrait;
use App\Domain\ModelTrait\UpdatedAtTrait;
use App\Domain\User\Model\AdminUserInterface;
use App\Domain\Security\PermissionRoleInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 * @example | id | name                | description             |
 *          | -- | ------------------- | ----------------------- |
 *          |  1 | PROJECT_MANAGER     | Gérer les projets       |
 *          | 2  | DOMAIN_EDITOR       | Éditer un domaine       |
 *          | 3  | NOTIFICATION\_ADMIN | Gérer les notifications |
 */
abstract class PermissionRole implements PermissionRoleInterface
{
    use CreatedAtTrait ;
    use UpdatedAtTrait ;
    
    protected int|string|null $id=null;

    protected ?string $name=null;

    protected ?string $description=null;

    protected ?string $context=null ;

    protected  ?AdminUserInterface $createdBy = null;

    /**
     * Get the value of id
     */
    public function getId(): int|string|null
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
     * Get the value of context
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * Set the value of context
     */
    public function setContext(?string $context): void
    {
        $this->context = $context;
    }

    public function getCreatedBy(): ?AdminUserInterface
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?AdminUserInterface $user): void
    {
        $this->createdBy = $user;
    }

    public function __toString():string
    {
        return $this->getName();
    }
}
