<?php

namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    /**
     * Trouve tous les thèmes actifs
     */
    public function findAllActive()
    {
        return $this->createQueryBuilder('t')
            ->where('t.active = :active')
            ->setParameter('active', true)
            ->orderBy('t.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}