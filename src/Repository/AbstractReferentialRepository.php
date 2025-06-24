<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository abstrait pour les référentiels avec intégration énums
 * Pattern appliqué: Abstract Repository Pattern (patterns.md)
 * 
 * Factorise les méthodes communes des repositories référentiels :
 * - PlancheRepository, TypePriseRepository, TypeVenteRepository
 * - ThemeRepository, PochetteIndivRepository, PochetteFratrieRepository
 * 
 * @template T of object
 * @extends ServiceEntityRepository<T>
 */
abstract class AbstractReferentialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * Retourne la classe d'enum associée à ce repository
     * À implémenter dans chaque repository concret
     */
    abstract protected function getEnumClass(): string;

    /**
     * Crée une entité depuis un enum
     * À implémenter dans chaque repository concret
     */
    abstract protected function createEntityFromEnum(object $enum): object;

    /**
     * Retourne l'alias de requête pour ce repository
     * À implémenter dans chaque repository concret
     */
    abstract protected function getQueryAlias(): string;

    /**
     * Trouve toutes les entités actives triées par libellé
     * Pattern: Repository - requête optimisée
     */
    public function findAllActive(): array
    {
        $alias = $this->getQueryAlias();
        
        return $this->createQueryBuilder($alias)
            ->andWhere($alias . '.active = :active')
            ->setParameter('active', true)
            ->orderBy($alias . '.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les entités avec comptage des prises de vue
     * Pattern: Repository - jointure optimisée
     */
    public function findAllWithPriseDeVueCount(): array
    {
        $alias = $this->getQueryAlias();
        
        return $this->createQueryBuilder($alias)
            ->select($alias)
            ->where($alias . '.active = :active')
            ->setParameter('active', true)
            ->orderBy($alias . '.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les entités basées sur les énums
     * Pattern: Repository avec intégration énums
     */
    public function findEnumBasedTypes(): array
    {
        $enumClass = $this->getEnumClass();
        $enumLibelles = array_map(
            fn($enum) => $enum->getLibelle(), 
            $enumClass::getDefaultValues()
        );

        $alias = $this->getQueryAlias();
        
        return $this->createQueryBuilder($alias)
            ->andWhere($alias . '.libelle IN (:enumLibelles)')
            ->setParameter('enumLibelles', $enumLibelles)
            ->orderBy($alias . '.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les entités personnalisées (non-énums)
     * Pattern: Repository avec logique métier
     */
    public function findCustomTypes(): array
    {
        $enumClass = $this->getEnumClass();
        $enumLibelles = array_map(
            fn($enum) => $enum->getLibelle(), 
            $enumClass::getDefaultValues()
        );

        $alias = $this->getQueryAlias();
        $qb = $this->createQueryBuilder($alias)
            ->orderBy($alias . '.libelle', 'ASC');

        if (!empty($enumLibelles)) {
            $qb->andWhere($alias . '.libelle NOT IN (:enumLibelles)')
               ->setParameter('enumLibelles', $enumLibelles);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Vérifie si un libellé correspond à un enum
     */
    public function isEnumLibelle(string $libelle): bool
    {
        $enumClass = $this->getEnumClass();
        
        foreach ($enumClass::getDefaultValues() as $enum) {
            if ($enum->getLibelle() === $libelle) {
                return true;
            }
        }
        return false;
    }

    /**
     * Trouve ou crée une entité depuis un enum
     * Pattern: Repository - factory method
     */
    public function findOrCreateFromEnum(object $enum): object
    {
        $entity = $this->findOneBy(['libelle' => $enum->getLibelle()]);
        
        if (!$entity) {
            $entity = $this->createEntityFromEnum($enum);
            
            $this->getEntityManager()->persist($entity);
            $this->getEntityManager()->flush();
        }
        
        return $entity;
    }

    /**
     * Recherche avec filtres et pagination
     * Pattern: Repository - requête complexe optimisée
     */
    public function findByCriteria(
        ?string $search = null,
        ?bool $active = null,
        string $sort = 'libelle',
        string $order = 'ASC',
        int $page = 1,
        int $limit = 20
    ): array {
        $alias = $this->getQueryAlias();
        $qb = $this->createQueryBuilder($alias);

        // Filtres de recherche
        if ($search) {
            $qb->andWhere('(' . $alias . '.libelle LIKE :search OR ' . $alias . '.description LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($active !== null) {
            $qb->andWhere($alias . '.active = :active')
               ->setParameter('active', $active);
        }

        // Tri
        $allowedSorts = ['libelle', 'description', 'active'];
        if (in_array($sort, $allowedSorts)) {
            $qb->orderBy($alias . '.' . $sort, $order);
        } else {
            $qb->orderBy($alias . '.libelle', 'ASC');
        }

        // Pagination
        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte le total pour la pagination
     */
    public function countByCriteria(?string $search = null, ?bool $active = null): int
    {
        $alias = $this->getQueryAlias();
        $qb = $this->createQueryBuilder($alias)
            ->select('COUNT(' . $alias . '.id)');

        if ($search) {
            $qb->andWhere('(' . $alias . '.libelle LIKE :search OR ' . $alias . '.description LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($active !== null) {
            $qb->andWhere($alias . '.active = :active')
               ->setParameter('active', $active);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Recherche d'entités par libellé
     */
    public function findByLibelle(string $search): array
    {
        $alias = $this->getQueryAlias();
        
        return $this->createQueryBuilder($alias)
            ->where($alias . '.libelle LIKE :search')
            ->andWhere($alias . '.active = :active')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('active', true)
            ->orderBy($alias . '.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les entités les plus utilisées
     */
    public function findMostUsed(int $limit = 10): array
    {
        $alias = $this->getQueryAlias();
        
        return $this->createQueryBuilder($alias)
            ->andWhere($alias . '.active = :active')
            ->setParameter('active', true)
            ->orderBy($alias . '.libelle', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Sauvegarde une entité
     * Pattern: Repository - méthode de persistance
     */
    public function save(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime une entité
     * Pattern: Repository - méthode de suppression
     */
    public function remove(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
} 