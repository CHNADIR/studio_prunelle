<?php

namespace App\Repository;

use App\Entity\Ecole;
use App\Entity\PriseDeVue;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour la gestion des prises de vue
 */
class PriseDeVueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriseDeVue::class);
    }
    
    /**
     * Création d'une QueryBuilder de base pour les prises de vue
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
        
        if (in_array('pochettes', $associations)) {
            $qb->leftJoin('p.pochettesIndiv', 'pi')
               ->leftJoin('p.pochettesFratrie', 'pf')
               ->leftJoin('p.planches', 'pl')
               ->addSelect('pi', 'pf', 'pl');
        }
        
        return $qb;
    }
    
    /**
     * Trouve toutes les prises de vue avec pagination
     */
    public function findAllWithPagination(
        int $page = 1, 
        int $limit = 20, 
        array $associations = ['typePrise', 'theme']
    ): array {
        $query = $this->createBaseQueryBuilder($associations)
            ->orderBy('p.datePdv', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
            
        return [
            'results' => $query->getQuery()->getResult(),
            'totalItems' => $this->count([]),
        ];
    }

    /**
     * Trouve les prises de vue d'un photographe spécifique (selon pattern)
     */
    public function findByPhotographe(
        User $photographe, 
        int $page = 1, 
        int $limit = 20, 
        array $associations = ['typePrise', 'theme']
    ): array {
        $query = $this->createBaseQueryBuilder($associations)
            ->andWhere('p.photographe = :photographe')
            ->setParameter('photographe', $photographe)
            ->orderBy('p.datePdv', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
            
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
     * Trouve les dernières prises de vue d'une école (pour fiche école)
     */
    public function findLastByEcole(Ecole $ecole, int $limit = 5): array
    {
        return $this->createBaseQueryBuilder(['typePrise', 'theme', 'pochettes'])
            ->andWhere('p.ecole = :ecole')
            ->setParameter('ecole', $ecole)
            ->orderBy('p.datePdv', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Recherche de prises de vue avec filtres multiples
     */
    public function findByCriteria(array $criteria): array
    {
        $qb = $this->createBaseQueryBuilder(['typePrise', 'theme']);
        
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
            $qb->andWhere('p.datePdv >= :dateDebut')
               ->setParameter('dateDebut', $criteria['dateDebut']);
        }
        
        if (!empty($criteria['dateFin'])) {
            $qb->andWhere('p.datePdv <= :dateFin')
               ->setParameter('dateFin', $criteria['dateFin']);
        }
        
        return $qb->orderBy('p.datePdv', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Trouve une prise de vue par ID avec tous les détails
     */
    public function findOneWithAllDetails(int $id): ?PriseDeVue
    {
        return $this->createBaseQueryBuilder(['typePrise', 'typeVente', 'theme', 'pochettes'])
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    /**
     * Query Builder pour les prises du photographe connecté
     */
    public function qbMesPrises(User $photographe): QueryBuilder
    {
        return $this->createBaseQueryBuilder(['typePrise', 'theme'])
            ->andWhere('p.photographe = :photographe')
            ->setParameter('photographe', $photographe)
            ->orderBy('p.datePdv', 'DESC');
    }

    /**
     * Statistiques pour dashboard
     */
    public function getStats(): array
    {
        $total = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $thisMonth = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.datePdv >= :startMonth')
            ->setParameter('startMonth', new \DateTime('first day of this month'))
            ->getQuery()
            ->getSingleScalarResult();

        $totalEleves = $this->createQueryBuilder('p')
            ->select('SUM(p.nbEleves)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total_prises' => $total,
            'ce_mois' => $thisMonth,
            'total_eleves' => $totalEleves ?: 0
        ];
    }

    /**
     * Calcul du chiffre d'affaires total
     */
    public function getChiffreAffaires(?\DateTime $dateDebut = null, ?\DateTime $dateFin = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('SUM(p.prixEcole) as ca_ecole', 'SUM(p.prixParent) as ca_parent');

        if ($dateDebut) {
            $qb->andWhere('p.datePdv >= :dateDebut')
               ->setParameter('dateDebut', $dateDebut);
        }

        if ($dateFin) {
            $qb->andWhere('p.datePdv <= :dateFin')
               ->setParameter('dateFin', $dateFin);
        }

        $result = $qb->getQuery()->getSingleResult();

        return [
            'ca_ecole' => $result['ca_ecole'] ?: '0.00',
            'ca_parent' => $result['ca_parent'] ?: '0.00',
            'ca_total' => bcadd($result['ca_ecole'] ?: '0.00', $result['ca_parent'] ?: '0.00', 2)
        ];
    }

    /**
     * Trouve les prises de vue selon des critères avec pagination
     */
    public function findByCriteriaWithPagination(
        array $criteria, 
        int $page = 1, 
        int $limit = 20
    ): array {
        $qb = $this->createBaseQueryBuilder(['typePrise', 'theme']);
        
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
            $qb->andWhere('p.datePdv >= :dateDebut')
               ->setParameter('dateDebut', $criteria['dateDebut']);
        }
        
        if (!empty($criteria['dateFin'])) {
            $qb->andWhere('p.datePdv <= :dateFin')
               ->setParameter('dateFin', $criteria['dateFin']);
        }
        
        // Compter le total
        $countQb = clone $qb;
        $countQb->select('COUNT(p.id)');
        $totalItems = (int)$countQb->getQuery()->getSingleScalarResult();
        
        // Appliquer la pagination
        $qb->orderBy('p.datePdv', 'DESC')
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);
           
        return [
            'results' => $qb->getQuery()->getResult(),
            'totalItems' => $totalItems,
        ];
    }

    public function save(PriseDeVue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PriseDeVue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}