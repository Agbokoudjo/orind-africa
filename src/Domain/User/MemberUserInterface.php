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

interface MemberUserInterface extends BaseUserInterface
{
    public function getSkills(): ?array;

    public function setSkills(?array $skills): void;

    public function getInterests(): ?array;
    
    public function setInterests(?array $interests): void;
}
