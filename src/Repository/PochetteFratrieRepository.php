<?php

namespace App\Repository;

use App\Entity\PochetteFratrie;
use App\Enum\PochetteFratrieEnum;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour PochetteFratrie avec intégration énums
 * Pattern appliqué: Repository Pattern (patterns.md)
 * @extends AbstractReferentialRepository<PochetteFratrie>
 */
class PochetteFratrieRepository extends AbstractReferentialRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PochetteFratrie::class);
    }

    /**
     * Retourne la classe d'enum associée
     */
    protected function getEnumClass(): string
    {
        return PochetteFratrieEnum::class;
    }

    /**
     * Crée une entité PochetteFratrie depuis un enum
     */
    protected function createEntityFromEnum(object $enum): PochetteFratrie
    {
        $pochetteFratrie = new PochetteFratrie();
        $pochetteFratrie->setLibelle($enum->getLibelle());
        $pochetteFratrie->setDescription($enum->getDescription());
        $pochetteFratrie->setActive(true);
        
        return $pochetteFratrie;
    }

    /**
     * Retourne l'alias de requête pour ce repository
     */
    protected function getQueryAlias(): string
    {
        return 'pf';
    }
} 