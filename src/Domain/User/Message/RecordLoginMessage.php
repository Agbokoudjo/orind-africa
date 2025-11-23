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

namespace App\Domain\User\Message;

use App\Domain\User\Enum\UserType;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 */
final readonly class RecordLoginMessage
{
    public function __construct(
        public int|string $userId,
        public UserType $userType,
        public string $lastLoginIp
    ) {}
}