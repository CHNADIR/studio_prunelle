<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250619130145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE planche (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, type VARCHAR(20) NOT NULL, formats JSON NOT NULL, prix_ecole NUMERIC(10, 2) NOT NULL, prix_parents NUMERIC(10, 2) NOT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_planche (prise_de_vue_id INT NOT NULL, planche_id INT NOT NULL, INDEX IDX_1F5DCA2F5C08B7F7 (prise_de_vue_id), INDEX IDX_1F5DCA2FDA8652A8 (planche_id), PRIMARY KEY(prise_de_vue_id, planche_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_planche_fratrie (prise_de_vue_id INT NOT NULL, planche_id INT NOT NULL, INDEX IDX_C3AED0625C08B7F7 (prise_de_vue_id), INDEX IDX_C3AED062DA8652A8 (planche_id), PRIMARY KEY(prise_de_vue_id, planche_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_9775E7086C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE type_prise (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8E06B77F6C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE type_vente (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8686ADBB6C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche ADD CONSTRAINT FK_1F5DCA2F5C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche ADD CONSTRAINT FK_1F5DCA2FDA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche_fratrie ADD CONSTRAINT FK_C3AED0625C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche_fratrie ADD CONSTRAINT FK_C3AED062DA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD type_prise_id INT DEFAULT NULL, ADD type_vente_id INT DEFAULT NULL, ADD theme_id INT DEFAULT NULL, ADD nb_classes INT DEFAULT NULL, ADD prix_ecole NUMERIC(10, 2) DEFAULT NULL, ADD prix_parents NUMERIC(10, 2) DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED817B0B7799 FOREIGN KEY (type_prise_id) REFERENCES type_prise (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED81B03830F6 FOREIGN KEY (type_vente_id) REFERENCES type_vente (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED8159027487 FOREIGN KEY (theme_id) REFERENCES theme (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED817B0B7799 ON prise_de_vue (type_prise_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED81B03830F6 ON prise_de_vue (type_vente_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED8159027487 ON prise_de_vue (theme_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED8159027487
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED817B0B7799
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED81B03830F6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche DROP FOREIGN KEY FK_1F5DCA2F5C08B7F7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche DROP FOREIGN KEY FK_1F5DCA2FDA8652A8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche_fratrie DROP FOREIGN KEY FK_C3AED0625C08B7F7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche_fratrie DROP FOREIGN KEY FK_C3AED062DA8652A8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE planche
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prise_de_vue_planche
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prise_de_vue_planche_fratrie
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
            DROP INDEX IDX_3EAEED817B0B7799 ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3EAEED81B03830F6 ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3EAEED8159027487 ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP type_prise_id, DROP type_vente_id, DROP theme_id, DROP nb_classes, DROP prix_ecole, DROP prix_parents, DROP created_at, DROP updated_at
        SQL);
    }
}
