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
     * Trouve les planches actives par type
     * @return Planche[] Returns an array of active Planche objects by type
     */
    public function findActiveByType(PlancheUsage|string $type): array
    {
        $value = $type instanceof BackedEnum ? $type->value : $type;
        
        return $this->createQueryBuilder($this->getAlias())
            ->andWhere($this->getAlias() . '.actif = :actif')
            ->andWhere($this->getAlias() . '.type = :type')
            ->setParameter('actif', true)
            ->setParameter('type', $value)
            ->orderBy($this->getAlias() . '.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * QueryBuilder pour les planches actives par type
     */
    public function createActivesByTypeQueryBuilder(
        PlancheUsage|string $type
    ): QueryBuilder {
        $value = $type instanceof BackedEnum ? $type->value : $type;

        return $this->createQueryBuilder('p')
            ->where('p.actif = true')
            ->andWhere('p.type = :type')
            ->setParameter('type', $value)
            ->orderBy('p.nom', 'ASC');
    }
    
    /**
     * Trouve toutes les planches actives (override from parent)
     */
    public function findAllActive(string $orderField = 'nom', string $orderDirection = 'ASC'): array
    {
        return $this->createQueryBuilder($this->getAlias())
            ->where($this->getAlias() . '.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy($this->getAlias() . '.' . $orderField, $orderDirection)
            ->getQuery()
            ->getResult();
    }

    /**
     * Étend la méthode createSearchQueryBuilder pour ajouter les critères de recherche spécifiques
     */
    protected function createSearchQueryBuilder(array $criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        
        // Utiliser 'actif' au lieu de 'active' pour l'entité Planche
        if (isset($criteria['actif'])) {
            $qb->andWhere($this->getAlias() . '.actif = :actif')
               ->setParameter('actif', $criteria['actif']);
        }
        
        if (isset($criteria['nom'])) {
            $qb->andWhere($this->getAlias() . '.nom LIKE :nom')
               ->setParameter('nom', '%' . $criteria['nom'] . '%');
        }
        
        if (isset($criteria['type'])) {
            $qb->andWhere($this->getAlias() . '.type = :type')
               ->setParameter('type', $criteria['type']);
        }
        
        if (isset($criteria['usage'])) {
            $qb->andWhere($this->getAlias() . '.usage = :usage')
               ->setParameter('usage', $criteria['usage']);
        }
        
        return $qb->orderBy($this->getAlias() . '.nom', 'ASC');
    }

    /**
     * Trouve les planches utilisées dans des prises de vue
     */
    public function findUsedInPrisesDeVue(): array
    {
        return $this->createQueryBuilder($this->getAlias())
            ->join($this->getAlias() . '.prisesDeVue', 'pdv')
            ->orderBy($this->getAlias() . '.nom', 'ASC')
            ->groupBy($this->getAlias() . '.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques sur les planches
     */
    public function getPlancheStats(): array
    {
        $total = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $actives = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.actif = true')
            ->getQuery()
            ->getSingleScalarResult();

        $parType = $this->createQueryBuilder('p')
            ->select('p.type, COUNT(p.id) as count')
            ->where('p.actif = true')
            ->groupBy('p.type')
            ->getQuery()
            ->getResult();

        return [
            'total' => (int)$total,
            'actives' => (int)$actives,
            'inactives' => (int)$total - (int)$actives,
            'parType' => $parType
        ];
    }
}