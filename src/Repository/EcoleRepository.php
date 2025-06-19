<?php

namespace App\Repository;

use App\Entity\Ecole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EcoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ecole::class);
    }

    /**
     * Exemple de requête personnalisée pour pagination ou tri
     */
    public function findAllOrderedByName()
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}