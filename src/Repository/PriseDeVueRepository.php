<?php

namespace App\Repository;

use App\Entity\Ecole;
use App\Entity\PriseDeVue;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PriseDeVueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriseDeVue::class);
    }
    
    /**
     * Création d'une QueryBuilder de base pour les prises de vue
     * Optimisée en utilisant des jointures conditionnelles
     */
    private function createBaseQueryBuilder(array $associations = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');
        
        // Jointures de base toujours nécessaires
        $qb->leftJoin('p.ecole', 'e')
           ->leftJoin('p.photographe', 'u')
           ->addSelect('e', 'u');
        
        // Jointures conditionnelles pour optimiser les performances
        if (in_array('typePrise', $associations)) {
            $qb->leftJoin('p.typePrise', 'tp')
               ->addSelect('tp');
        }
        
        if (in_array('typeVente', $associations)) {
            $qb->leftJoin('p.typeVente', 'tv')
               ->addSelect('tv');
        }
        
        if (in_array('theme', $associations)) {
            $qb->leftJoin('p.theme', 't')
               ->addSelect('t');
        }
        
        if (in_array('planches', $associations)) {
            $qb->leftJoin('p.planchesIndividuelles', 'pi')
               ->leftJoin('p.planchesFratries', 'pf')
               ->addSelect('pi', 'pf');
        }
        
        return $qb;
    }
    
    /**
     * Trouve toutes les prises de vue avec pagination
     * @param array $associations Relations à charger
     */
    public function findAllPaginated(
        int $page = 1, 
        int $limit = 10, 
        array $associations = ['typePrise', 'theme']
    ): array {
        $query = $this->createBaseQueryBuilder($associations)
            ->orderBy('p.date', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
            
        return [
            'results' => $query->getQuery()->getResult(),
            'totalItems' => $this->count([]),
        ];
    }

    /**
     * Trouve les prises de vue d'un photographe spécifique
     * Utilise un hint de query pour optimiser le chargement
     */
    public function findByPhotographe(
        User $photographe, 
        int $page = 1, 
        int $limit = 10, 
        array $associations = ['typePrise', 'theme']
    ): array {
        $query = $this->createBaseQueryBuilder($associations)
            ->andWhere('p.photographe = :photographe')
            ->setParameter('photographe', $photographe)
            ->orderBy('p.date', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        
        $query->getQuery()->setHint(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD, true);
            
        $countQb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.photographe = :photographe')
            ->setParameter('photographe', $photographe);
            
        return [
            'results' => $query->getQuery()->getResult(),
            'totalItems' => (int)$countQb->getQuery()->getSingleScalarResult(),
        ];
    }

    /**
     * Trouve les prises de vue d'une école spécifique
     */
    public function findByEcole(
        Ecole $ecole, 
        int $page = 1, 
        int $limit = 10, 
        array $associations = ['typePrise', 'theme']
    ): array {
        $query = $this->createBaseQueryBuilder($associations)
            ->andWhere('p.ecole = :ecole')
            ->setParameter('ecole', $ecole)
            ->orderBy('p.date', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
            
        $countQb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.ecole = :ecole')
            ->setParameter('ecole', $ecole);
            
        return [
            'results' => $query->getQuery()->getResult(),
            'totalItems' => (int)$countQb->getQuery()->getSingleScalarResult(),
        ];
    }
    
    /**
     * Recherche de prises de vue avec filtres multiples
     * Optimisée pour n'inclure que les jointures nécessaires
     */
    public function search(
        array $criteria, 
        int $page = 1, 
        int $limit = 10
    ): array {
        // Déterminer quelles jointures sont nécessaires
        $associations = ['typePrise', 'theme'];
        
        $qb = $this->createBaseQueryBuilder($associations);
        
        if (!empty($criteria['ecole'])) {
            $qb->andWhere('p.ecole = :ecole')
               ->setParameter('ecole', $criteria['ecole']);
        }
        
        if (!empty($criteria['photographe'])) {
            $qb->andWhere('p.photographe = :photographe')
               ->setParameter('photographe', $criteria['photographe']);
        }
        
        if (!empty($criteria['typePrise'])) {
            $qb->andWhere('p.typePrise = :typePrise')
               ->setParameter('typePrise', $criteria['typePrise']);
        }
        
        if (!empty($criteria['dateDebut'])) {
            $qb->andWhere('p.date >= :dateDebut')
               ->setParameter('dateDebut', $criteria['dateDebut']);
        }
        
        if (!empty($criteria['dateFin'])) {
            $qb->andWhere('p.date <= :dateFin')
               ->setParameter('dateFin', $criteria['dateFin']);
        }
        
        $countQb = clone $qb;
        $countQb->select('COUNT(p.id)');
        
        $qb->orderBy('p.date', 'DESC')
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);
        
        return [
            'results' => $qb->getQuery()->getResult(),
            'totalItems' => (int)$countQb->getQuery()->getSingleScalarResult(),
        ];
    }

    /**
     * Trouve une prise de vue par ID avec tous les détails
     * pour l'affichage complet (optimisation)
     */
    public function findOneWithAllDetails(int $id): ?PriseDeVue
    {
        return $this->createBaseQueryBuilder(['typePrise', 'typeVente', 'theme', 'planches'])
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    /**
     * QueryBuilder personnalisé pour les prises de vue d'un photographe
     */
    public function qbMesPrises(User $photographe)
    {
        return $this->createQueryBuilder('p')
            ->where('p.photographe = :photographe')
            ->setParameter('photographe', $photographe)
            ->orderBy('p.date', 'DESC');
    }
}