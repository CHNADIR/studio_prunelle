<?php

namespace App\Repository;

use App\Entity\Ecole; // Ajoutez cette ligne
use App\Entity\PriseDeVue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PriseDeVue>
 */
class PriseDeVueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriseDeVue::class);
    }

    /**
     * Récupère les prises de vue récentes pour une école donnée.
     * @return PriseDeVue[]
     */
    public function findRecentByEcole(Ecole $ecole, int $limit = 5): array
    {
        return $this->createQueryBuilder('pdv')
            ->andWhere('pdv.ecole = :ecole')
            ->setParameter('ecole', $ecole)
            ->orderBy('pdv.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return PriseDeVue[] Returns an array of PriseDeVue objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PriseDeVue
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
