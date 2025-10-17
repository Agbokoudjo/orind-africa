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

use App\Domain\User\Enum\UserType;
use App\Domain\Security\PermissionRoleInterface;
use App\Domain\Security\UserPermissionRoleInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 * @example | user_id | permission_role_id   | scope (optionnel) |
 *          | --------| -------------------- | ----------------- |
 *          | 10      | 1                    | null              |
 *          | 10      | 2                    | domaine=Finance   |
 *          | 11      | 1                    | null              |
 */
abstract class UserPermissionRole implements UserPermissionRoleInterface
{
    protected int|string|null $id=null ;

    protected readonly UserType $userType;

    protected readonly int|string $userId;

    protected readonly PermissionRoleInterface $roles;

    protected readonly \DateTimeInterface $assignedAt ;

    protected  ?string $scope=null;

    final public function getId(): int|string|null{

        return $this->id ;
    }

    public function setRoles(PermissionRoleInterface $roles): void
    {
        $this->roles =$roles;
    }

    public function getRoles(): PermissionRoleInterface
    {
        return $this->roles;
    }

    public function getUserId():int|string{

        return $this->userId ;
    }

    public function setUserId(string $userId): void
    {
        $this->userId=$userId;
    }

    final public function getUserType():UserType {

        return $this->userType ;
    }

    final public function getPermissionRole():PermissionRoleInterface{

        return $this->roles ;
    }

    final public function  getAssignedAt():\DateTimeInterface{

        return $this->assignedAt ;
    }

    final public function getScope():?string{

        return $this->scope ;
    }

    final public function setScop(?string $_scope):void{

        $this->scope=$_scope ;
    }
    final public function setUserType(UserType $_userType):void{

        $this->userType=$_userType ;
    }

    final public function setAssignedAt(\DateTimeInterface $_assignedAt):void{

        $this->assignedAt = $_assignedAt ;
    }

    final public function SetPermissionRole(PermissionRoleInterface $_roles):void{

        $this->roles =$_roles ;
    }
}
