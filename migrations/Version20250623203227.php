<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250623203227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_pochette_indiv (prise_de_vue_id INT NOT NULL, pochette_indiv_id INT NOT NULL, INDEX IDX_BBA604985C08B7F7 (prise_de_vue_id), INDEX IDX_BBA60498D247EFE8 (pochette_indiv_id), PRIMARY KEY(prise_de_vue_id, pochette_indiv_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_pochette_fratrie (prise_de_vue_id INT NOT NULL, pochette_fratrie_id INT NOT NULL, INDEX IDX_990D328C5C08B7F7 (prise_de_vue_id), INDEX IDX_990D328C3B259061 (pochette_fratrie_id), PRIMARY KEY(prise_de_vue_id, pochette_fratrie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_planche (prise_de_vue_id INT NOT NULL, planche_id INT NOT NULL, INDEX IDX_1F5DCA2F5C08B7F7 (prise_de_vue_id), INDEX IDX_1F5DCA2FDA8652A8 (planche_id), PRIMARY KEY(prise_de_vue_id, planche_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_pochette_indiv ADD CONSTRAINT FK_BBA604985C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_pochette_indiv ADD CONSTRAINT FK_BBA60498D247EFE8 FOREIGN KEY (pochette_indiv_id) REFERENCES pochette_indiv (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_pochette_fratrie ADD CONSTRAINT FK_990D328C5C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_pochette_fratrie ADD CONSTRAINT FK_990D328C3B259061 FOREIGN KEY (pochette_fratrie_id) REFERENCES pochette_fratrie (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche ADD CONSTRAINT FK_1F5DCA2F5C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche ADD CONSTRAINT FK_1F5DCA2FDA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED81AB3353FF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED81DA8652A8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP FOREIGN KEY FK_3EAEED81FC719224
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3EAEED81FC719224 ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3EAEED81DA8652A8 ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3EAEED81AB3353FF ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP planche_id, DROP pochetteIndiv_id, DROP pochetteFratrie_id
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_pochette_indiv DROP FOREIGN KEY FK_BBA604985C08B7F7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_pochette_indiv DROP FOREIGN KEY FK_BBA60498D247EFE8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_pochette_fratrie DROP FOREIGN KEY FK_990D328C5C08B7F7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_pochette_fratrie DROP FOREIGN KEY FK_990D328C3B259061
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche DROP FOREIGN KEY FK_1F5DCA2F5C08B7F7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche DROP FOREIGN KEY FK_1F5DCA2FDA8652A8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prise_de_vue_pochette_indiv
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prise_de_vue_pochette_fratrie
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prise_de_vue_planche
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD planche_id INT DEFAULT NULL, ADD pochetteIndiv_id INT DEFAULT NULL, ADD pochetteFratrie_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED81AB3353FF FOREIGN KEY (pochetteIndiv_id) REFERENCES pochette_indiv (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED81DA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD CONSTRAINT FK_3EAEED81FC719224 FOREIGN KEY (pochetteFratrie_id) REFERENCES pochette_fratrie (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED81FC719224 ON prise_de_vue (pochetteFratrie_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED81DA8652A8 ON prise_de_vue (planche_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3EAEED81AB3353FF ON prise_de_vue (pochetteIndiv_id)
        SQL);
    }
}
