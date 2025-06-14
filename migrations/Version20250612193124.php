<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250612193124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE ecole (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(5) DEFAULT NULL, genre VARCHAR(50) NOT NULL, nom VARCHAR(255) NOT NULL, rue VARCHAR(255) NOT NULL, ville VARCHAR(100) NOT NULL, code_postal VARCHAR(10) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE planche (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) DEFAULT NULL, categorie VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue (id INT AUTO_INCREMENT NOT NULL, ecole_id INT DEFAULT NULL, type_prise_id INT DEFAULT NULL, theme_id INT NOT NULL, type_vente_id INT NOT NULL, date DATETIME NOT NULL, photographe VARCHAR(255) NOT NULL, nombre_eleves INT NOT NULL, nombre_classes INT NOT NULL, prix_ecole NUMERIC(10, 2) NOT NULL, prix_parent NUMERIC(10, 2) NOT NULL, commentaire LONGTEXT DEFAULT NULL, INDEX IDX_3EAEED8177EF1B1E (ecole_id), INDEX IDX_3EAEED817B0B7799 (type_prise_id), INDEX IDX_3EAEED8159027487 (theme_id), INDEX IDX_3EAEED81B03830F6 (type_vente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_individuel_planches (prise_de_vue_id INT NOT NULL, planche_id INT NOT NULL, INDEX IDX_EA6FAC235C08B7F7 (prise_de_vue_id), INDEX IDX_EA6FAC23DA8652A8 (planche_id), PRIMARY KEY(prise_de_vue_id, planche_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_fratrie_planches (prise_de_vue_id INT NOT NULL, planche_id INT NOT NULL, INDEX IDX_61E7B3155C08B7F7 (prise_de_vue_id), INDEX IDX_61E7B315DA8652A8 (planche_id), PRIMARY KEY(prise_de_vue_id, planche_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE type_prise (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE type_vente (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED8177EF1B1E FOREIGN KEY (ecole_id) REFERENCES ecole (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED817B0B7799 FOREIGN KEY (type_prise_id) REFERENCES type_prise (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED8159027487 FOREIGN KEY (theme_id) REFERENCES theme (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED81B03830F6 FOREIGN KEY (type_vente_id) REFERENCES type_vente (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_individuel_planches ADD CONSTRAINT FK_EA6FAC235C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_individuel_planches ADD CONSTRAINT FK_EA6FAC23DA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_fratrie_planches ADD CONSTRAINT FK_61E7B3155C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_fratrie_planches ADD CONSTRAINT FK_61E7B315DA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED8177EF1B1E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED817B0B7799
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED8159027487
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED81B03830F6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_individuel_planches DROP FOREIGN KEY FK_EA6FAC235C08B7F7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_individuel_planches DROP FOREIGN KEY FK_EA6FAC23DA8652A8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_fratrie_planches DROP FOREIGN KEY FK_61E7B3155C08B7F7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_fratrie_planches DROP FOREIGN KEY FK_61E7B315DA8652A8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ecole
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE planche
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prise_individuel_planches
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prise_fratrie_planches
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE theme
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE type_prise
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE type_vente
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
    }
}
