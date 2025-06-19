<?php

namespace App\Repository;

use App\Entity\TypeVente;
use Doctrine\Persistence\ManagerRegistry;

class TypeVenteRepository extends AbstractReferentialRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeVente::class);
    }
    
    /**
     * Retourne l'alias de table pour les requêtes
     */
    protected function getAlias(): string
    {
        return 'tv';
    }
    
    /**
     * Trouve les types de vente utilisés dans des prises de vue
     */
    public function findUsedInPrisesDeVue(): array
    {
        return $this->createQueryBuilder($this->getAlias())
            ->join($this->getAlias() . '.prisesDeVue', 'p')
            ->orderBy($this->getAlias() . '.nom', 'ASC')
            ->groupBy($this->getAlias() . '.id')
            ->getQuery()
            ->getResult();
    }
}