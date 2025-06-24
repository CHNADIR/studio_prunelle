<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250623090238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE pochette_fratrie (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(200) NOT NULL, description LONGTEXT DEFAULT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_2A6CA2DDA4D60759 (libelle), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE pochette_indiv (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(200) NOT NULL, description LONGTEXT DEFAULT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_3C1DE59CA4D60759 (libelle), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches_individuelles DROP FOREIGN KEY FK_106FBB1D5C08B7F7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches_individuelles DROP FOREIGN KEY FK_106FBB1DDA8652A8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches_fratries DROP FOREIGN KEY FK_6A2AD6E35C08B7F7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches_fratries DROP FOREIGN KEY FK_6A2AD6E3DA8652A8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prise_de_vue_planches_individuelles
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prise_de_vue_planches_fratries
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ecole ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, CHANGE genre genre VARCHAR(100) NOT NULL, CHANGE active active TINYINT(1) DEFAULT 1 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9786AAC77153098 ON ecole (code)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planche ADD libelle VARCHAR(200) NOT NULL, DROP nom, DROP type, DROP formats, DROP prix_ecole, DROP prix_parents, DROP `usage`, DROP type_vente, DROP installation, DROP pochette_plastique, DROP cartonnage, DROP bon_commande, DROP type_specifique, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE commentaire description LONGTEXT DEFAULT NULL, CHANGE actif active TINYINT(1) DEFAULT 1 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_ABF41FB8A4D60759 ON planche (libelle)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED817B0B7799
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED81B03830F6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3EAEED817B0B7799 ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3EAEED81B03830F6 ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD planche_id INT DEFAULT NULL, ADD typePrise_id INT DEFAULT NULL, ADD typeVente_id INT DEFAULT NULL, ADD pochetteIndiv_id INT DEFAULT NULL, ADD pochetteFratrie_id INT DEFAULT NULL, DROP type_prise_id, DROP type_vente_id, DROP classes, CHANGE date date_pdv DATE NOT NULL, CHANGE prix_parents prix_parent NUMERIC(10, 2) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED8113EBBCBB FOREIGN KEY (typePrise_id) REFERENCES type_prise (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED81D8D8FBD4 FOREIGN KEY (typeVente_id) REFERENCES type_vente (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED81AB3353FF FOREIGN KEY (pochetteIndiv_id) REFERENCES pochette_indiv (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED81FC719224 FOREIGN KEY (pochetteFratrie_id) REFERENCES pochette_fratrie (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED81DA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED8113EBBCBB ON prise_de_vue (typePrise_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED81D8D8FBD4 ON prise_de_vue (typeVente_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED81AB3353FF ON prise_de_vue (pochetteIndiv_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED81FC719224 ON prise_de_vue (pochetteFratrie_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED81DA8652A8 ON prise_de_vue (planche_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_9775E7086C6E55B5 ON theme
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theme ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, CHANGE active active TINYINT(1) DEFAULT 1 NOT NULL, CHANGE nom libelle VARCHAR(100) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9775E708A4D60759 ON theme (libelle)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8E06B77F6C6E55B5 ON type_prise
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type_prise ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, CHANGE active active TINYINT(1) DEFAULT 1 NOT NULL, CHANGE nom libelle VARCHAR(100) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8E06B77FA4D60759 ON type_prise (libelle)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8686ADBB6C6E55B5 ON type_vente
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type_vente ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, CHANGE active active TINYINT(1) DEFAULT 1 NOT NULL, CHANGE nom libelle VARCHAR(100) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8686ADBBA4D60759 ON type_vente (libelle)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD username VARCHAR(50) NOT NULL, ADD actif TINYINT(1) DEFAULT 1 NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED81FC719224
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED81AB3353FF
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_planches_individuelles (prise_de_vue_id INT NOT NULL, planche_id INT NOT NULL, INDEX IDX_106FBB1DDA8652A8 (planche_id), INDEX IDX_106FBB1D5C08B7F7 (prise_de_vue_id), PRIMARY KEY(prise_de_vue_id, planche_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_planches_fratries (prise_de_vue_id INT NOT NULL, planche_id INT NOT NULL, INDEX IDX_6A2AD6E3DA8652A8 (planche_id), INDEX IDX_6A2AD6E35C08B7F7 (prise_de_vue_id), PRIMARY KEY(prise_de_vue_id, planche_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches_individuelles ADD CONSTRAINT FK_106FBB1D5C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches_individuelles ADD CONSTRAINT FK_106FBB1DDA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches_fratries ADD CONSTRAINT FK_6A2AD6E35C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches_fratries ADD CONSTRAINT FK_6A2AD6E3DA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE pochette_fratrie
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE pochette_indiv
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_9775E708A4D60759 ON theme
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theme DROP created_at, DROP updated_at, CHANGE active active TINYINT(1) NOT NULL, CHANGE libelle nom VARCHAR(100) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9775E7086C6E55B5 ON theme (nom)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8D93D649F85E0677 ON user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP username, DROP actif, DROP created_at, DROP updated_at
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_ABF41FB8A4D60759 ON planche
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planche ADD nom VARCHAR(255) NOT NULL, ADD type VARCHAR(255) NOT NULL, ADD formats JSON NOT NULL, ADD prix_ecole NUMERIC(10, 2) NOT NULL, ADD prix_parents NUMERIC(10, 2) NOT NULL, ADD `usage` VARCHAR(7) NOT NULL, ADD type_vente VARCHAR(255) DEFAULT NULL, ADD installation VARCHAR(255) DEFAULT NULL, ADD pochette_plastique VARCHAR(255) DEFAULT NULL, ADD cartonnage VARCHAR(255) DEFAULT NULL, ADD bon_commande VARCHAR(255) DEFAULT NULL, ADD type_specifique VARCHAR(255) NOT NULL, DROP libelle, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE active actif TINYINT(1) DEFAULT 1 NOT NULL, CHANGE description commentaire LONGTEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8686ADBBA4D60759 ON type_vente
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type_vente DROP created_at, DROP updated_at, CHANGE active active TINYINT(1) NOT NULL, CHANGE libelle nom VARCHAR(100) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8686ADBB6C6E55B5 ON type_vente (nom)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8E06B77FA4D60759 ON type_prise
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type_prise DROP created_at, DROP updated_at, CHANGE active active TINYINT(1) NOT NULL, CHANGE libelle nom VARCHAR(100) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8E06B77F6C6E55B5 ON type_prise (nom)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_9786AAC77153098 ON ecole
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ecole DROP created_at, DROP updated_at, CHANGE genre genre VARCHAR(50) NOT NULL, CHANGE active active TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED8113EBBCBB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED81D8D8FBD4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED81DA8652A8
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3EAEED8113EBBCBB ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3EAEED81D8D8FBD4 ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3EAEED81AB3353FF ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3EAEED81FC719224 ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3EAEED81DA8652A8 ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD type_prise_id INT DEFAULT NULL, ADD type_vente_id INT DEFAULT NULL, ADD classes VARCHAR(255) DEFAULT NULL, DROP planche_id, DROP typePrise_id, DROP typeVente_id, DROP pochetteIndiv_id, DROP pochetteFratrie_id, CHANGE date_pdv date DATE NOT NULL, CHANGE prix_parent prix_parents NUMERIC(10, 2) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED817B0B7799 FOREIGN KEY (type_prise_id) REFERENCES type_prise (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED81B03830F6 FOREIGN KEY (type_vente_id) REFERENCES type_vente (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED817B0B7799 ON prise_de_vue (type_prise_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED81B03830F6 ON prise_de_vue (type_vente_id)
        SQL);
    }
}
