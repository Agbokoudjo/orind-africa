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
enum MinisterRole: string
{
    case ADMIN = 'ROLE_ADMIN';
    case GROUP_MANAGER = 'ROLE_GROUP_MANAGER';
    case PROJECT_MANAGER = 'ROLE_PROJECT_MANAGER';
    case CONTENT_MODERATOR = 'ROLE_CONTENT_MODERATOR';
    case NOTIFICATION_MANAGER = 'ROLE_NOTIFICATION_MANAGER';
    case REPORT_VIEWER = 'ROLE_REPORT_VIEWER';
    case OWN_GROUP_ACCESS = 'ROLE_OWN_GROUP_ACCESS';

    /**
     * Retourne tous les rôles sous forme de tableau string
     */
    public static function values(): array
    {
        return array_map(fn(self $role) => $role->value, self::cases());
    }
}
