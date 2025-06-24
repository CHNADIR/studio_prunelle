<?php

namespace App\Repository;

use App\Entity\Theme;
use App\Enum\ThemeEnum;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour Theme avec intégration énums
 * Pattern appliqué: Repository Pattern (patterns.md)
 * @extends AbstractReferentialRepository<Theme>
 */
class ThemeRepository extends AbstractReferentialRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    /**
     * Retourne la classe d'enum associée
     */
    protected function getEnumClass(): string
    {
        return ThemeEnum::class;
    }

    /**
     * Crée une entité Theme depuis un enum
     */
    protected function createEntityFromEnum(object $enum): Theme
    {
        $theme = new Theme();
        $theme->setLibelle($enum->getLibelle());
        $theme->setDescription($enum->getDescription());
        $theme->setActive(true);
        
        return $theme;
    }

    /**
     * Retourne l'alias de requête pour ce repository
     */
    protected function getQueryAlias(): string
    {
        return 't';
    }
} 