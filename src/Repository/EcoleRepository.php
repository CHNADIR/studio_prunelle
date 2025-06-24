<?php

namespace App\Repository;

use App\Entity\Ecole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour la gestion des écoles
 */
class EcoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ecole::class);
    }

    /**
     * Trouve toutes les écoles actives
     * 
     * @return Ecole[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.active = :active')
            ->setParameter('active', true)
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche d'une école par nom ou par critère (selon cahier des charges)
     * 
     * @param string $search
     * @return Ecole[]
     */
    public function findByNomOrCritere(string $search): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.nom LIKE :search')
            ->orWhere('e.code LIKE :search')
            ->orWhere('e.ville LIKE :search')
            ->orWhere('e.genre LIKE :search')
            ->andWhere('e.active = :active')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('active', true)
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une école par son code (unique)
     * 
     * @param string $code
     * @return Ecole|null
     */
    public function findByCode(string $code): ?Ecole
    {
        return $this->createQueryBuilder('e')
            ->where('e.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les écoles avec leurs dernières prises de vue pour l'affichage BDD
     * 
     * @return Ecole[]
     */
    public function findAllWithLastPrisesDeVue(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.prisesDeVue', 'pdv')
            ->addSelect('pdv')
            ->where('e.active = :active')
            ->setParameter('active', true)
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche avancée d'écoles avec pagination et tri
     * 
     * @param array $criteria Critères de recherche
     * @param int $page Page courante
     * @param int $limit Nombre d'éléments par page
     * @param string $sortBy Champ de tri
     * @param string $sortOrder Ordre de tri (asc/desc)
     * @return array
     */
    public function findByCriteriaWithPagination(
        array $criteria = [], 
        int $page = 1, 
        int $limit = 10, 
        string $sortBy = 'nom', 
        string $sortOrder = 'asc'
    ): array {
        $qb = $this->createQueryBuilder('e');
        
        // Recherche textuelle
        if (!empty($criteria['search'])) {
            $search = $criteria['search'];
            $qb->andWhere('e.nom LIKE :search OR e.code LIKE :search OR e.ville LIKE :search OR e.genre LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        
        // Filtre par statut
        if (isset($criteria['active'])) {
            $qb->andWhere('e.active = :active')
               ->setParameter('active', $criteria['active']);
        }
        
        // Validation et application du tri
        $allowedSorts = ['nom', 'code', 'ville', 'genre', 'active', 'createdAt'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'nom';
        }
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtoupper($sortOrder) : 'ASC';
        
        // Query pour compter le total
        $countQb = clone $qb;
        $totalItems = (int) $countQb->select('COUNT(e.id)')->getQuery()->getSingleScalarResult();
        
        // Query avec pagination et tri
        $results = $qb->orderBy('e.' . $sortBy, $sortOrder)
                     ->setFirstResult(($page - 1) * $limit)
                     ->setMaxResults($limit)
                     ->getQuery()
                     ->getResult();
        
        return [
            'results' => $results,
            'totalItems' => $totalItems,
            'totalPages' => (int) ceil($totalItems / $limit),
        ];
    }

    /**
     * Trouve toutes les écoles triées par nom pour les listes
     * 
     * @return Ecole[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les écoles ayant des prises de vue
     * Pattern: Repository - requête optimisée
     */
    public function countWithPrisesDeVue(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(DISTINCT e.id)')
            ->innerJoin('e.prisesDeVue', 'pdv')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(Ecole $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ecole $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}