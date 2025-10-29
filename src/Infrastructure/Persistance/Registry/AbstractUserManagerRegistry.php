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

namespace App\Infrastructure\Persistance\Registry;

use App\Domain\User\Enum\UserType;
use App\Domain\User\Model\BaseUserInterface;
use App\Domain\User\Manager\UserManagerInterface;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use App\Infrastructure\Doctrine\Entity\User\MemberUser;
use App\Domain\User\Exception\UserManagerNotFoundException;
use App\Infrastructure\Persistance\CustomUserManagerInterface;
use App\Domain\User\Service\Registry\UserManagerRegistryInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
abstract class AbstractUserManagerRegistry implements UserManagerRegistryInterface
{
    /**
     * @var iterable<string, UserManagerInterface>
     */
    protected iterable $managers = [];

    /**
     * @param iterable<CustomUserManagerInterface> $managers
     */
    protected function __construct(
        iterable $managers
    ) {
        foreach ($managers as $manager) {
            $this->managers[$manager->getFQCN()] = $manager;
        }
    }

    public function getByUser(string|BaseUserInterface $user): UserManagerInterface
    {
        $class = is_object($user) ? get_class($user) : $user;

        if (!isset($this->managers[$class])) {
            throw new UserManagerNotFoundException($class);
        }

        return $this->managers[$class];
    }

    public function getByUserType(UserType $userType): UserManagerInterface
    {
        $targetClass = match ($userType) {
            
            UserType::ADMIN => AdminUser::class,

            UserType::MEMBER => MemberUser::class,

            // Gestion de l'exception pour les types non supportés
            default => throw new UserManagerNotFoundException(
                sprintf('Aucun modèle/manager supporté trouvé pour UserType: %s', $userType->name)
            ),
        };

        return $this->getByUser($targetClass);
    }
}
