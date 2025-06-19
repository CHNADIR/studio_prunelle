<?php

namespace App\Repository;

use App\Entity\PriseDeVue;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PriseDeVueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriseDeVue::class);
    }

    /**
     * Trouve les prises de vue d'un photographe spécifique
     */
    public function findByPhotographe(User $photographe)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.photographe = :photographe')
            ->setParameter('photographe', $photographe)
            ->orderBy('p.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les prises de vue d'une école spécifique
     */
    public function findByEcole($ecoleId)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.ecole = :ecoleId')
            ->setParameter('ecoleId', $ecoleId)
            ->orderBy('p.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}