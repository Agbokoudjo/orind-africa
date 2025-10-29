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

namespace App\Domain\User\Service\Registry;

use App\Domain\User\Enum\UserType;
use App\Domain\User\Model\BaseUserInterface;
use App\Domain\User\Manager\UserManagerInterface;
use App\Domain\User\Exception\UserManagerNotFoundException;

interface UserManagerRegistryInterface
{
    /**
     * Récupère le gestionnaire d'utilisateurs approprié pour un type utilisateur .
     *
     * @param string|BaseUserInterface $user L'utilisateur ou class
     * 
     * @return UserManagerInterface Le gestionnaire correspondant
     * 
     * @throws UserManagerNotFoundException Si aucun gestionnaire n'est trouvé
     */
    public function getByUserType(UserType $userType): UserManagerInterface ;

    /**
     * Récupère le gestionnaire d'utilisateurs approprié pour un utilisateur donné ou par le nom
     *  fqcn de la class.
     *
     * @param string|BaseUserInterface $user L'utilisateur ou class
     * 
     * @return UserManagerInterface Le gestionnaire correspondant
     * 
     * @throws UserManagerNotFoundException Si aucun gestionnaire n'est trouvé
     */
    public function getByUser(string|BaseUserInterface $class_or_object): UserManagerInterface;
}