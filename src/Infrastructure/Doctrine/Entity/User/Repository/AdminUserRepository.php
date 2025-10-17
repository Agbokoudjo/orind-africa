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

namespace App\Infrastructure\Doctrine\Entity\User\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class AdminUserRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        #[Target('data.respository.cache')]
        private readonly  TagAwareCacheInterface $dataCacheUser){
        parent::__construct($managerRegistry,AdminUser::class);
    }

    public function createQueryBuilderForUsersWithRole(
        string $roleName = "ROLE_MINISTER",
        bool $isEnabled = true
    ): QueryBuilder {
        // Le paramètre de rôle doit être encodé en JSON pour être comparé au tableau JSONB de la DB
        $roleJsonParam = json_encode([$roleName]);

        return $this->createQueryBuilder('u')
            ->where('u.enabled = :enabled')
            ->andWhere('JSONB_CONTAINS(u.roles, :role) =true')
            ->setParameter('enabled', $isEnabled)
            ->setParameter('role',  $roleJsonParam)
        ;

        // createQueryBuilder('u')
        //     ->where('u.enabled = :enabled')
        //     ->andWhere("JSONB_CONTAINS(u.roles, :role) = true")
        //     ->setParameter('enabled', true)
        //     ->setParameter('role', sprintf('"%s"', "ROLE_MINISTER"))
    }

    /**
     * Récupère le QueryBuilder pour les utilisateurs actifs n'ayant AUCUN des rôles spécifiés.
     * * @param array<string> $exclude_roles Rôles à exclure (ex: ['ROLE_FOUNDER'])
     */
    protected function createQueryBuilderForEnabledUsersExcludingRoles(
        array $exclude_roles = ['ROLE_FOUNDER']
    ): QueryBuilder {
    
        $roleJsonParam = json_encode($exclude_roles);

        return $this->createQueryBuilder('u')
            ->where('u.enabled = :enabled')

            // Utilisation de l'opérateur NOT (JSONB_CONTAINS)
            // La fonction DQL JSONB_CONTAINS se traduit en SQL 'u.roles @> :role'
            ->andWhere('JSONB_CONTAINS(u.roles, :role) = false')

            ->setParameter('enabled', true)
            ->setParameter('role', $roleJsonParam)
        ;
    }

    /**
     * Récupère un tableau associatif d'utilisateurs actifs n'ayant AUCUN des rôles spécifiés, 
     * formaté pour un champ de formulaire : ['username' => 'id'].
     *
     * @param array<string> $exclude_roles Rôles à exclure (par défaut: ['ROLE_FOUNDER']).
     * @return array<string, int> Tableau [Username => ID].
     */
    public function findByUserWithExcludRole(array $exclude_roles = ['ROLE_FOUNDER']): array
    {
        $cache_key_admin_user=join("_", $exclude_roles);
        return $this->dataCacheUser
                      ->get($cache_key_admin_user,function(ItemInterface $item) use($exclude_roles):array{
                            $queryBuilder = $this->createQueryBuilderForEnabledUsersExcludingRoles($exclude_roles);
                            $users = $queryBuilder->getQuery()->getResult();

                            $userChoices = [];

                            /** @var AdminUser $user */
                            foreach ($users as $user) {
                                
                                $userChoices[$user->getUsername()] = $user->getId();
                            }

                            return $userChoices;
                      });
        
    }
}
