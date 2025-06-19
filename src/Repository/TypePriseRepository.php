<?php

namespace App\Repository;

use App\Entity\TypePrise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TypePriseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypePrise::class);
    }

    /**
     * Trouve tous les types de prise actifs
     */
    public function findAllActive()
    {
        return $this->createQueryBuilder('tp')
            ->where('tp.active = :active')
            ->setParameter('active', true)
            ->orderBy('tp.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}