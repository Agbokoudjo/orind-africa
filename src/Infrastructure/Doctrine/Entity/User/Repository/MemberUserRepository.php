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

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use App\Infrastructure\Doctrine\Entity\User\MemberUser;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class MemberUserRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        #[Target('data.respository.cache')]
        private readonly  TagAwareCacheInterface $dataCache
        )
    {
        parent::__construct($managerRegistry, MemberUser::class);
    }

    /**
    * @return MemberUser[] Returns an array of Modules objects
    */
    public function findByForUsers(bool $isEnabled=true,?int $limitResults=null): array
    {
        $limit = $limitResults ?? 'no_limit';
        $cache_key = \sprintf('member_user_enabled_%s_limit_%s', $isEnabled ? 'true' : 'false', $limit);

        /**
         * @var array
         */
        $result=$this->dataCache
                    ->get($cache_key,function(ItemInterface $item) use ($isEnabled, $limitResults): array{

                        $item->tag(['member_user']);

                        if ($limitResults) {
                            $item->tag(['member_user_limit']);
                        }

                       return $this->createQueryBuilder('m')
                                ->andWhere('m.enabled = :enabled')
                                ->setParameter('enabled', $isEnabled)
                                ->orderBy('m.id', 'DESC')
                                ->setMaxResults($limitResults)
                                ->getQuery()
                                ->getResult();

        });

       return  $result ;
    }
   

    public function invalidateTags(){

        $this->dataCache->invalidateTags(['member_user']);
    }
    /**
     * Récupère un tableau de MemberUser actifs formaté pour un champ de formulaire (ChoiceType).
     * Le format est [Username => ID].
     *
     * @return array<string, int> Tableau des choix [Nom affiché => ID].
     */
    public function getMemberUserChoices(): array
    {
        $memberUsers = $this->findByForUsers();

        if(empty($memberUsers)){
            return [];
        }
        $memberUserChoices = [];

        /** @var MemberUser $member */ 
        foreach ($memberUsers as $member) {

            if ($member instanceof MemberUser) {
                $memberUserChoices[$member->getUsername()] = $member->getId();
            }
        }

        return $memberUserChoices;
    }
}
