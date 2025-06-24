<?php

namespace App\Repository;

use App\Entity\Planche;
use App\Enum\PlancheEnum;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour Planche avec intégration énums
 * Pattern appliqué: Repository Pattern (patterns.md)
 * @extends AbstractReferentialRepository<Planche>
 */
class PlancheRepository extends AbstractReferentialRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planche::class);
    }

    /**
     * Retourne la classe d'enum associée
     */
    protected function getEnumClass(): string
    {
        return PlancheEnum::class;
    }

    /**
     * Crée une entité Planche depuis un enum
     */
    protected function createEntityFromEnum(object $enum): Planche
    {
        $planche = new Planche();
        $planche->setLibelle($enum->getLibelle()); 
        $planche->setDescription($enum->getDescription());
        $planche->setActive(true);
        
        return $planche;
    }

    /**
     * Retourne l'alias de requête pour ce repository
     */
    protected function getQueryAlias(): string
    {
        return 'p';
    }

    /**
     * Trouve les planches utilisées dans les prises de vue
     * Méthode spécifique à PlancheRepository
     */
    public function findUsedInPrisesDeVue(): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.prisesDeVue', 'pdv')
            ->andWhere('p.active = :active')
            ->setParameter('active', true)
            ->groupBy('p.id')
            ->orderBy('p.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des planches
     * Méthode spécifique à PlancheRepository
     */
    public function getPlancheStats(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id) as total')
            ->addSelect('COUNT(CASE WHEN p.active = 1 THEN 1 END) as active')
            ->addSelect('COUNT(CASE WHEN p.active = 0 THEN 1 END) as inactive');

        $result = $qb->getQuery()->getSingleResult();

        return [
            'total' => (int) $result['total'],
            'active' => (int) $result['active'],
            'inactive' => (int) $result['inactive']
        ];
    }
} 