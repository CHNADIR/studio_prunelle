<?php

namespace App\Repository;

use App\Entity\TypeVente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TypeVenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeVente::class);
    }

    /**
     * Trouve tous les types de vente actifs
     */
    public function findAllActive()
    {
        return $this->createQueryBuilder('tv')
            ->where('tv.active = :active')
            ->setParameter('active', true)
            ->orderBy('tv.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}