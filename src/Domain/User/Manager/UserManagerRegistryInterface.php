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

namespace App\Domain\User\Manager;

use App\Domain\User\BaseUserInterface;
use App\Domain\User\Manager\UserManagerInterface;
use App\Domain\Exception\UserManagerNotFoundException;

interface UserManagerRegistryInterface
{
    /**
     * Undocumented function
     *
     * @param string|BaseUserInterface $class
     * @throws UserManagerNotFoundException if class manager don't found 
     * @return UserManagerInterface
     */
    public function get(string|BaseUserInterface $class):UserManagerInterface;
}