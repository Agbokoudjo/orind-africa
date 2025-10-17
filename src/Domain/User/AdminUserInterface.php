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

namespace App\Domain\User;

use App\Domain\User\BaseUserInterface;

interface AdminUserInterface extends BaseUserInterface
{
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public function isSuperAdmin(): bool;

    public function setSuperAdmin(bool $boolean): void;

    public function isFounder(): bool;

    public function isCoFounder(): bool ;

    public function isMinister():bool ;

    public function isAdmin(): bool;

}
