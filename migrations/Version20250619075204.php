<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250620071234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des entités référentielles TypePrise, TypeVente et Theme';
    }

    public function up(Schema $schema): void
    {
        // Création table type_prise
        $this->addSql('CREATE TABLE type_prise (
            id INT AUTO_INCREMENT NOT NULL, 
            nom VARCHAR(100) NOT NULL, 
            active TINYINT(1) NOT NULL, 
            UNIQUE INDEX UNIQ_ABCDEF12_nom (nom), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Création table type_vente
        $this->addSql('CREATE TABLE type_vente (
            id INT AUTO_INCREMENT NOT NULL, 
            nom VARCHAR(100) NOT NULL, 
            active TINYINT(1) NOT NULL, 
            UNIQUE INDEX UNIQ_ABCDEF34_nom (nom), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Création table theme
        $this->addSql('CREATE TABLE theme (
            id INT AUTO_INCREMENT NOT NULL, 
            nom VARCHAR(100) NOT NULL, 
            active TINYINT(1) NOT NULL, 
            UNIQUE INDEX UNIQ_ABCDEF56_nom (nom), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Ajouter colonnes à prise_de_vue
        $this->addSql('ALTER TABLE prise_de_vue 
            ADD type_prise_id INT DEFAULT NULL, 
            ADD type_vente_id INT DEFAULT NULL, 
            ADD theme_id INT DEFAULT NULL');
        
        // Ajouter clés étrangères
        $this->addSql('ALTER TABLE prise_de_vue 
            ADD CONSTRAINT FK_PRISE_DE_VUE_type_prise FOREIGN KEY (type_prise_id) REFERENCES type_prise (id), 
            ADD CONSTRAINT FK_PRISE_DE_VUE_type_vente FOREIGN KEY (type_vente_id) REFERENCES type_vente (id), 
            ADD CONSTRAINT FK_PRISE_DE_VUE_theme FOREIGN KEY (theme_id) REFERENCES theme (id)');
        
        // Ajouter index
        $this->addSql('CREATE INDEX IDX_PRISE_DE_VUE_type_prise ON prise_de_vue (type_prise_id)');
        $this->addSql('CREATE INDEX IDX_PRISE_DE_VUE_type_vente ON prise_de_vue (type_vente_id)');
        $this->addSql('CREATE INDEX IDX_PRISE_DE_VUE_theme ON prise_de_vue (theme_id)');
    }

    public function down(Schema $schema): void
    {
        // Supprimer contraintes de clé étrangère
        $this->addSql('ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_PRISE_DE_VUE_type_prise');
        $this->addSql('ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_PRISE_DE_VUE_type_vente');
        $this->addSql('ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_PRISE_DE_VUE_theme');
        
        // Supprimer index
        $this->addSql('DROP INDEX IDX_PRISE_DE_VUE_type_prise ON prise_de_vue');
        $this->addSql('DROP INDEX IDX_PRISE_DE_VUE_type_vente ON prise_de_vue');
        $this->addSql('DROP INDEX IDX_PRISE_DE_VUE_theme ON prise_de_vue');
        
        // Supprimer colonnes
        $this->addSql('ALTER TABLE prise_de_vue DROP type_prise_id, DROP type_vente_id, DROP theme_id');
        
        // Supprimer tables
        $this->addSql('DROP TABLE type_prise');
        $this->addSql('DROP TABLE type_vente');
        $this->addSql('DROP TABLE theme');
    }
}