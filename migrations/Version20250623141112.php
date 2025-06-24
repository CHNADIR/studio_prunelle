<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration de nettoyage - Option A : Sélection unique
 * 
 * Cette migration supprime les tables de liaison ManyToMany qui étaient définies
 * dans l'entité PriseDeVue mais jamais utilisées, créant une incohérence.
 * 
 * Après cette migration, PriseDeVue utilise uniquement des relations ManyToOne
 * pour des sélections uniques, conformément à l'Option A choisie.
 */
final class Version20250623141112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression des tables de liaison ManyToMany inutilisées pour PriseDeVue (Option A: sélection unique)';
    }

    public function up(Schema $schema): void
    {
        // === SUPPRESSION DES TABLES MANY-TO-MANY INUTILISÉES ===
        
        // Ces tables avaient été créées automatiquement par Doctrine mais ne correspondent
        // pas à l'architecture finale retenue (sélection unique via ManyToOne)
        
        $this->addSql(<<<'SQL'
            DROP TABLE IF EXISTS prise_de_vue_pochettes_indiv
        SQL);
        
        $this->addSql(<<<'SQL'
            DROP TABLE IF EXISTS prise_de_vue_pochettes_fratrie
        SQL);
        
        $this->addSql(<<<'SQL'
            DROP TABLE IF EXISTS prise_de_vue_planches
        SQL);
        
        // Vérifier que les colonnes ManyToOne existent bien dans prise_de_vue
        // (elles devraient déjà être présentes depuis les migrations précédentes)
        
        // Ajouter un commentaire dans la table pour documenter l'architecture
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue COMMENT = 'Table principale des prises de vue - Relations ManyToOne uniquement (sélection unique)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // En cas de rollback, recréer les tables ManyToMany
        // (bien que cette architecture ne soit plus recommandée)
        
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_pochettes_indiv (
                prise_de_vue_id INT NOT NULL, 
                pochette_indiv_id INT NOT NULL, 
                INDEX IDX_572A90175C08B7F7 (prise_de_vue_id), 
                INDEX IDX_572A9017D247EFE8 (pochette_indiv_id), 
                PRIMARY KEY(prise_de_vue_id, pochette_indiv_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = 'Table de liaison - OBSOLÈTE'
        SQL);
        
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_pochettes_fratrie (
                prise_de_vue_id INT NOT NULL, 
                pochette_fratrie_id INT NOT NULL, 
                INDEX IDX_D2740DD95C08B7F7 (prise_de_vue_id), 
                INDEX IDX_D2740DD93B259061 (pochette_fratrie_id), 
                PRIMARY KEY(prise_de_vue_id, pochette_fratrie_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = 'Table de liaison - OBSOLÈTE'
        SQL);
        
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_planches (
                prise_de_vue_id INT NOT NULL, 
                planche_id INT NOT NULL, 
                INDEX IDX_B0C0AF985C08B7F7 (prise_de_vue_id), 
                INDEX IDX_B0C0AF98DA8652A8 (planche_id), 
                PRIMARY KEY(prise_de_vue_id, planche_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = 'Table de liaison - OBSOLÈTE'
        SQL);
        
        // Ajouter les contraintes de clés étrangères
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_pochettes_indiv 
            ADD CONSTRAINT FK_572A90175C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON DELETE CASCADE,
            ADD CONSTRAINT FK_572A9017D247EFE8 FOREIGN KEY (pochette_indiv_id) REFERENCES pochette_indiv (id) ON DELETE CASCADE
        SQL);
        
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_pochettes_fratrie 
            ADD CONSTRAINT FK_D2740DD95C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON DELETE CASCADE,
            ADD CONSTRAINT FK_D2740DD93B259061 FOREIGN KEY (pochette_fratrie_id) REFERENCES pochette_fratrie (id) ON DELETE CASCADE
        SQL);
        
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches 
            ADD CONSTRAINT FK_B0C0AF985C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON DELETE CASCADE,
            ADD CONSTRAINT FK_B0C0AF98DA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON DELETE CASCADE
        SQL);
        
        // Supprimer le commentaire
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue COMMENT = ''
        SQL);
    }
}
