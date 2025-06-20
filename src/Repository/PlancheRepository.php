<?php

namespace App\Repository;

use App\Entity\Planche;
use App\Enum\PlancheUsage;
use BackedEnum;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PlancheRepository extends AbstractReferentialRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planche::class);
    }
    
    /**
     * Retourne l'alias de table pour les requêtes
     */
    protected function getAlias(): string
    {
        return 'p';
    }
    
    /**
     * @return Planche[] Returns an array of active Planche objects by type
     */
    public function findActiveByType(string $type): array
    {
        return $this->createQueryBuilder($this->getAlias())
            ->andWhere($this->getAlias() . '.active = :active')
            ->andWhere($this->getAlias() . '.type = :type')
            ->setParameter('active', true)
            ->setParameter('type', $type)
            ->orderBy($this->getAlias() . '.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
    /**
     * Étend la méthode createSearchQueryBuilder pour ajouter les critères de recherche spécifiques
     */
    protected function createSearchQueryBuilder(array $criteria): \Doctrine\ORM\QueryBuilder
    {
        $qb = parent::createSearchQueryBuilder($criteria);
        
        if (isset($criteria['type'])) {
            $qb->andWhere($this->getAlias() . '.type = :type')
               ->setParameter('type', $criteria['type']);
        }
        
        return $qb;
    }

    public function createActivesByTypeQueryBuilder(
        PlancheUsage|string $type   // accepte Enum *ou* string
    ): QueryBuilder {
        $value = $type instanceof BackedEnum ? $type->value : $type;

        return $this->createQueryBuilder('p')
            ->where('p.actif = true')
            ->andWhere('p.type = :type')
            ->setParameter('type', $value)
            ->orderBy('p.nom', 'ASC');
    }
}