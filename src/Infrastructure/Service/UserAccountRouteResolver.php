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

namespace App\Infrastructure\Service;

use App\Domain\User\Enum\UserType;
use App\Domain\User\Model\BaseUserInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class UserAccountRouteResolver
{
    /**
     * Noms des routes de connexion par type d'utilisateur.
     */
    private const LOGIN_ROUTES = [
        'admin'  => 'app_admin_user_login',
        'member' => 'app_member_user_login',
        'default' => 'app_user_login',
    ];

    /**
     * Noms des routes de réinitialisation de mot de passe par type.
     */
    private const RESET_PASSWORD_ROUTES = [
        'admin'  => 'app_admin_reset_password',
        'member' => 'app_member_reset_password',
        'default' => 'app_user_reset_password',
    ];

    /**
     * Noms des routes de modification d'email par type.
     */
    private const RESET_EMAIL_ROUTES = [
        'admin'  => 'app_admin_reset_email',
        'member' => 'app_member_reset_email',
        'default' => 'app_user_reset_email',
    ];

    public static function resolveLoginRouteNameByUser(BaseUserInterface $user): string
    {
        return match (true) {
            $user->hasRole('ROLE_ADMIN')  => 'app_admin_user_login',
            $user->hasRole('ROLE_MEMBER') => 'app_member_user_login',
            default                       => 'app_user_login',
        };
    }

    public static function resolveLoginRouteNameByType(UserType $userType): string
    {
        return match ($userType) {
            UserType::ADMIN  => 'app_admin_user_login',
            UserType::MEMBER => 'app_member_user_login',
            default                       => 'app_user_login',
        };
    }

    public static function resolveResetPasswordRouteName(BaseUserInterface $user): string
    {
        // TODO : logique à implémenter
        return 'app_user_reset_password';
    }

    public static function resolveResetEmailRouteName(BaseUserInterface $user): string
    {
        // TODO : logique à implémenter
        return 'app_user_reset_email';
    }
}
