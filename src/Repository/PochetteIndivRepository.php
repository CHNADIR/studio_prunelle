<?php

namespace App\Repository;

use App\Entity\PochetteIndiv;
use App\Enum\PochetteIndivEnum;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour PochetteIndiv avec intégration énums
 * Pattern appliqué: Repository Pattern (patterns.md)
 * @extends AbstractReferentialRepository<PochetteIndiv>
 */
class PochetteIndivRepository extends AbstractReferentialRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PochetteIndiv::class);
    }

    /**
     * Retourne la classe d'enum associée
     */
    protected function getEnumClass(): string
    {
        return PochetteIndivEnum::class;
    }

    /**
     * Crée une entité PochetteIndiv depuis un enum
     */
    protected function createEntityFromEnum(object $enum): PochetteIndiv
    {
        $pochetteIndiv = new PochetteIndiv();
        $pochetteIndiv->setLibelle($enum->getLibelle());
        $pochetteIndiv->setDescription($enum->getDescription());
        $pochetteIndiv->setActive(true);
        
        return $pochetteIndiv;
    }

    /**
     * Retourne l'alias de requête pour ce repository
     */
    protected function getQueryAlias(): string
    {
        return 'pi';
    }
} 