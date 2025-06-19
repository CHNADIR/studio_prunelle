<?php

namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Persistence\ManagerRegistry;

class ThemeRepository extends AbstractReferentialRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }
    
    /**
     * Retourne l'alias de table pour les requêtes
     */
    protected function getAlias(): string
    {
        return 't';
    }
    
    /**
     * Trouve les thèmes utilisés dans des prises de vue
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