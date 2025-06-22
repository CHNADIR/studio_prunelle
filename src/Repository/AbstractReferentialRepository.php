<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Classe abstraite pour les repositories de référentiels
 */
abstract class AbstractReferentialRepository extends ServiceEntityRepository
{
    /**
     * Retourne le nom court de l'alias de table pour les requêtes
     */
    abstract protected function getAlias(): string;
    
    /**
     * Trouve toutes les entités actives
     * 
     * @param string $orderField Champ de tri (défaut: 'nom')
     * @param string $orderDirection Direction du tri (défaut: 'ASC')
     * @return array
     */
    public function findAllActive(string $orderField = 'nom', string $orderDirection = 'ASC'): array
    {
        return $this->createQueryBuilder($this->getAlias())
            ->where($this->getAlias() . '.active = :active')
            ->setParameter('active', true)
            ->orderBy($this->getAlias() . '.' . $orderField, $orderDirection)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Trouve toutes les entités paginées
     * 
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @param string $orderField Champ de tri (défaut: 'nom')
     * @param string $orderDirection Direction du tri (défaut: 'ASC')
     * @return array [results, totalItems]
     */
    public function findAllPaginated(int $page = 1, int $limit = 10, string $orderField = 'nom', string $orderDirection = 'ASC'): array
    {
        $query = $this->createQueryBuilder($this->getAlias())
            ->orderBy($this->getAlias() . '.' . $orderField, $orderDirection)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        
        $countQuery = $this->createQueryBuilder($this->getAlias())
            ->select('COUNT(' . $this->getAlias() . '.id)');
        
        return [
            'results' => $query->getQuery()->getResult(),
            'totalItems' => (int)$countQuery->getQuery()->getSingleScalarResult()
        ];
    }
    
    /**
     * Recherche avec filtres
     * 
     * @param array $criteria Critères de recherche
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @return array [results, totalItems]
     */
    public function search(array $criteria, int $page = 1, int $limit = 10): array
    {
        $qb = $this->createSearchQueryBuilder($criteria);
        
        $countQb = clone $qb;
        $countQb->select('COUNT(' . $this->getAlias() . '.id)');
        
        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);
        
        return [
            'results' => $qb->getQuery()->getResult(),
            'totalItems' => (int)$countQb->getQuery()->getSingleScalarResult()
        ];
    }
    
    /**
     * Crée un QueryBuilder pour la recherche (à surcharger dans les classes filles)
     * 
     * @param array $criteria Critères de recherche
     * @return QueryBuilder
     */
    protected function createSearchQueryBuilder(array $criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        
        if (isset($criteria['active'])) {
            $qb->andWhere($this->getAlias() . '.active = :active')
               ->setParameter('active', $criteria['active']);
        }
        
        if (isset($criteria['nom'])) {
            $qb->andWhere($this->getAlias() . '.nom LIKE :nom')
               ->setParameter('nom', '%' . $criteria['nom'] . '%');
        }
        
        return $qb->orderBy($this->getAlias() . '.nom', 'ASC');
    }

    /**
     * Trouve les entités utilisées dans des prises de vue
     * Méthode commune pour éviter la duplication
     * 
     * @return array
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
}