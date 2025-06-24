<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration d'optimisation des performances - Index et contraintes
 * Basée sur l'analyse du cahier des charges Studio Prunelle
 */
final class Version20250623092827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des index de performance et contraintes de cohérence métier selon le cahier des charges';
    }

    public function up(Schema $schema): void
    {
        // === INDEX DE PERFORMANCE ===
        
        // Ajouter les index seulement s'ils n'existent pas déjà
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_prise_de_vue_date_pdv ON prise_de_vue (date_pdv)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_prise_de_vue_photographe_date ON prise_de_vue (photographe_id, date_pdv)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_prise_de_vue_ecole_date ON prise_de_vue (ecole_id, date_pdv)');
        
        // Index pour optimiser les recherches d'écoles actives
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_ecole_active_nom ON ecole (active, nom)');
        
        // Index pour optimiser les requêtes sur les référentiels actifs
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_pochette_indiv_active_libelle ON pochette_indiv (active, libelle)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_pochette_fratrie_active_libelle ON pochette_fratrie (active, libelle)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_planche_active_libelle ON planche (active, libelle)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_type_prise_active_libelle ON type_prise (active, libelle)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_type_vente_active_libelle ON type_vente (active, libelle)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_theme_active_libelle ON theme (active, libelle)');
        
        // Index pour optimiser les requêtes sur les utilisateurs actifs
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_user_actif ON user (actif)');
        
        // === INDEX DE RECHERCHE ===
        
        // Index de recherche textuelle sur les libellés
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_ecole_nom_search ON ecole (nom)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_user_email_search ON user (email)');
        
        // === CONTRAINTES DE COHÉRENCE MÉTIER ===
        
        // Vérifier et ajouter les contraintes seulement si elles n'existent pas
        $this->addSql('ALTER TABLE ecole ADD CONSTRAINT chk_code_ecole 
            CHECK (LENGTH(code) = 5 AND code REGEXP \'^[0-9]{5}$\')');
        
        $this->addSql('ALTER TABLE prise_de_vue ADD CONSTRAINT chk_nb_eleves_positif 
            CHECK (nb_eleves > 0)');
        
        $this->addSql('ALTER TABLE prise_de_vue ADD CONSTRAINT chk_prix_positifs 
            CHECK (prix_ecole >= 0 AND prix_parent >= 0)');
    }

    public function down(Schema $schema): void
    {
        // Suppression des contraintes
        $this->addSql('ALTER TABLE ecole DROP CONSTRAINT chk_code_ecole');
        $this->addSql('ALTER TABLE prise_de_vue DROP CONSTRAINT chk_nb_eleves_positif');
        $this->addSql('ALTER TABLE prise_de_vue DROP CONSTRAINT chk_prix_positifs');
        
        // Suppression des index
        $this->addSql('DROP INDEX idx_prise_de_vue_date_pdv ON prise_de_vue');
        $this->addSql('DROP INDEX idx_prise_de_vue_photographe_date ON prise_de_vue');
        $this->addSql('DROP INDEX idx_prise_de_vue_ecole_date ON prise_de_vue');
        $this->addSql('DROP INDEX idx_ecole_active_nom ON ecole');
        $this->addSql('DROP INDEX idx_pochette_indiv_active_libelle ON pochette_indiv');
        $this->addSql('DROP INDEX idx_pochette_fratrie_active_libelle ON pochette_fratrie');
        $this->addSql('DROP INDEX idx_planche_active_libelle ON planche');
        $this->addSql('DROP INDEX idx_type_prise_active_libelle ON type_prise');
        $this->addSql('DROP INDEX idx_type_vente_active_libelle ON type_vente');
        $this->addSql('DROP INDEX idx_theme_active_libelle ON theme');
        $this->addSql('DROP INDEX idx_user_actif ON user');
        $this->addSql('DROP INDEX idx_ecole_nom_search ON ecole');
        $this->addSql('DROP INDEX idx_user_email_search ON user');
    }
}
