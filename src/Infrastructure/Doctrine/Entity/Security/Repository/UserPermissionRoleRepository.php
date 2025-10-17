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

namespace App\Infrastructure\Doctrine\Entity\Security\Repository;

use Doctrine\ORM\Query\Parameter;
use App\Domain\User\Enum\UserType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Security\UserPermissionRoleRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Infrastructure\Doctrine\Entity\Security\UserPermissionRoleEntity;


/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class UserPermissionRoleRepository extends ServiceEntityRepository implements UserPermissionRoleRepositoryInterface
{
    public function __construct(ManagerRegistry $managerRegistry, private CacheInterface $cache)
    {
        parent::__construct($managerRegistry, UserPermissionRoleEntity::class);
    }

    public function findRoleNamesForUser(UserType $user_type,int|string $userId):array{

        $qb = $this->createQueryBuilder('upr')
            ->select('r.name')
            ->join('upr.roles', 'r')
            ->where('upr.userType = :type')
            ->andWhere('upr.userId = :id')
            ->setParameters(new ArrayCollection([
                new Parameter('type',$user_type),
                new Parameter('id',$userId)]));

        return array_column($qb->getQuery()->getScalarResult(), 'name');
    }


    /**
     * Vérifie si un utilisateur possède un rôle précis.
     *
     * @param UserType    $userType   ex: UserType::ADMIN
     * @param int|string  $userId     ID de l'utilisateur
     * @param string      $roleName   ex: "ROLE_PROJECT_MANAGER"
     */
    public function userHasRole(UserType $userType, int|string $userId, string $roleName): bool
    {
        // Clé unique pour le cache (ajustez le prefix si besoin)
        $cacheKey = sprintf(
            'upr_has_role_%s_%s_%s',
            $userType->value,
            $userId,
            $roleName
        );

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($userType, $userId, $roleName) {
            // durée de vie de 5 minutes (modifiable)
            $item->expiresAfter(300);

            $qb = $this->createQueryBuilder('upr')
                ->select('COUNT(upr.id)')
                ->join('upr.roles', 'r')
                ->where('upr.userType = :type')
                ->andWhere('upr.userId = :id')
                ->andWhere('r.name = :role')
               ->setParameters(new ArrayCollection([
                    new Parameter('type', $userType->value),
                    new Parameter('id',   $userId),
                    new Parameter('role', $roleName),
                ]));

            $count = (int) $qb->getQuery()->getSingleScalarResult();

            return $count > 0;
        });
    }
}
