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

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
interface UserProfileEditPermissionsInterface{

    public const PERMISSION_EDITOR_EMAIL    = 'EDITOR_EMAIL';
    public const PERMISSION_EDITOR_USERNAME = 'EDITOR_USERNAME';
    public const PERMISSION_EDITOR_PHONE    = 'EDITOR_PHONE';
    public const PERMISSION_CAN_EDIT_OWN_PROFILE= "CAN_EDIT_OWN_PROFILE";
    public const PERMISSION_EDITOR_USER_PROFILE = [
        self::PERMISSION_EDITOR_EMAIL,
        self::PERMISSION_EDITOR_USERNAME,
        self::PERMISSION_EDITOR_PHONE,
        self::PERMISSION_CAN_EDIT_OWN_PROFILE
    ];
    
}