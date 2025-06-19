<?php

namespace App\Repository;

use App\Entity\TypePrise;
use Doctrine\Persistence\ManagerRegistry;

class TypePriseRepository extends AbstractReferentialRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypePrise::class);
    }
    
    /**
     * Retourne l'alias de table pour les requêtes
     */
    protected function getAlias(): string
    {
        return 'tp';
    }
    
    /**
     * Trouve les types de prise utilisés dans des prises de vue
     */
    public function findUsedInPrisesDeVue(): array
    {
        return $this->createQueryBuilder('tp')
            ->join('tp.prisesDeVue', 'p')
            ->orderBy('tp.nom', 'ASC')
            ->groupBy('tp.id')
            ->getQuery()
            ->getResult();
    }
}