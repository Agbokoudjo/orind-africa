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

namespace App\Domain\User\Enum;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
enum UserClass: string
{
    case ADMIN  = 'admin';
    case SUPER_ADMIN  = 'super_admin';
    case CO_FOUNDER  = 'co_founder';
    case FOUNDER  = 'founder';
    case MEMBER = 'member';
    case USER   = 'user';

    // public function label(): string
    // {
    //     return match ($this) {
    //         self::ADMIN  => 'Administrateur',
    //         self::MEMBER => 'Membre',
    //         self::USER   => 'Utilisateur',
    //     };
    // }

    public static function fromRole(string $role): ?self
    {
        return match (true) {
            str_starts_with($role, 'ROLE_ADMIN')  => self::ADMIN,
            str_starts_with($role, 'ROLE_SUPER_ADMIN')  => self::SUPER_ADMIN,
            str_starts_with($role, 'ROLE_MEMBER') => self::MEMBER,
            str_starts_with($role, 'ROLE_CO_FOUNDER')  => self::CO_FOUNDER,
            str_starts_with($role, 'ROLE_FOUNDER') => self::FOUNDER,
            default                               => self::USER,
        };
    }
}
