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

use App\Domain\User\BaseUserInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class UserAccountRouteResolver
{
    public function resolveLoginRouteName(BaseUserInterface $user): string
    {
        return match (true) {
            $user->hasRole('ROLE_ADMIN')  => 'app_admin_user_login',
            $user->hasRole('ROLE_MEMBER') => 'app_member_user_login',
            default                       => 'app_user_login',
        };
    }

    public function resolveResetPasswordRouteName(BaseUserInterface $user): string
    {
        // TODO : logique à implémenter
        return 'app_user_reset_password';
    }

    public function resolveResetEmailRouteName(BaseUserInterface $user): string
    {
        // TODO : logique à implémenter
        return 'app_user_reset_email';
    }
}
