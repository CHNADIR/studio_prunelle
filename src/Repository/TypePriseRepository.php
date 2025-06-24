<?php

namespace App\Repository;

use App\Entity\TypePrise;
use App\Enum\TypePriseEnum;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour TypePrise avec intégration énums
 * Pattern appliqué: Repository Pattern (patterns.md)
 * @extends AbstractReferentialRepository<TypePrise>
 */
class TypePriseRepository extends AbstractReferentialRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypePrise::class);
    }

    /**
     * Retourne la classe d'enum associée
     */
    protected function getEnumClass(): string
    {
        return TypePriseEnum::class;
    }

    /**
     * Crée une entité TypePrise depuis un enum
     */
    protected function createEntityFromEnum(object $enum): TypePrise
    {
            $typePrise = new TypePrise();
            $typePrise->setLibelle($enum->getLibelle());
            $typePrise->setDescription($enum->getDescription());
            $typePrise->setActive(true);
        
        return $typePrise;
    }

    /**
     * Retourne l'alias de requête pour ce repository
     */
    protected function getQueryAlias(): string
    {
        return 'tp';
    }
}