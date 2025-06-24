<?php

namespace App\Repository;

use App\Entity\TypeVente;
use App\Enum\TypeVenteEnum;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour TypeVente avec intégration énums
 * Pattern appliqué: Repository Pattern (patterns.md)
 * @extends AbstractReferentialRepository<TypeVente>
 */
class TypeVenteRepository extends AbstractReferentialRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeVente::class);
    }

    /**
     * Retourne la classe d'enum associée
     */
    protected function getEnumClass(): string
    {
        return TypeVenteEnum::class;
    }

    /**
     * Crée une entité TypeVente depuis un enum
     */
    protected function createEntityFromEnum(object $enum): TypeVente
    {
        $typeVente = new TypeVente();
        $typeVente->setLibelle($enum->getLibelle());
        $typeVente->setDescription($enum->getDescription());
        $typeVente->setActive(true);
        
        return $typeVente;
    }

    /**
     * Retourne l'alias de requête pour ce repository
     */
    protected function getQueryAlias(): string
    {
        return 'tv';
    }

    /**
     * Trouve tous les types de vente avec comptage des prises de vue
     * Surcharge avec jointure spécifique pour TypeVente
     */
    public function findAllWithPriseDeVueCount(): array
    {
        return $this->createQueryBuilder('tv')
            ->select('tv', 'COUNT(pdv.id) as priseDeVueCount')
            ->leftJoin('tv.prisesDeVue', 'pdv')
            ->groupBy('tv.id')
            ->orderBy('tv.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche avec filtres et pagination - version spécialisée pour TypeVente
     * Surcharge avec jointure spécifique pour le comptage des prises de vue
     */
    public function findByCriteria(
        ?string $search = null,
        ?bool $active = null,
        string $sort = 'libelle',
        string $order = 'ASC',
        int $page = 1,
        int $limit = 20
    ): array {
        $qb = $this->createQueryBuilder('tv')
            ->select('tv', 'COUNT(pdv.id) as priseDeVueCount')
            ->leftJoin('tv.prisesDeVue', 'pdv')
            ->groupBy('tv.id');

        // Filtres de recherche
        if ($search) {
            $qb->andWhere('(tv.libelle LIKE :search OR tv.description LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($active !== null) {
            $qb->andWhere('tv.active = :active')
               ->setParameter('active', $active);
        }

        // Tri
        $allowedSorts = ['libelle', 'description', 'active', 'priseDeVueCount'];
        if (in_array($sort, $allowedSorts)) {
            $qb->orderBy('tv.' . $sort, $order);
        }

        // Pagination
        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}