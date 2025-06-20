<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250620152005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_planches_individuelles (prise_de_vue_id INT NOT NULL, planche_id INT NOT NULL, INDEX IDX_106FBB1D5C08B7F7 (prise_de_vue_id), INDEX IDX_106FBB1DDA8652A8 (planche_id), PRIMARY KEY(prise_de_vue_id, planche_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_planches_fratries (prise_de_vue_id INT NOT NULL, planche_id INT NOT NULL, INDEX IDX_6A2AD6E35C08B7F7 (prise_de_vue_id), INDEX IDX_6A2AD6E3DA8652A8 (planche_id), PRIMARY KEY(prise_de_vue_id, planche_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches_individuelles ADD CONSTRAINT FK_106FBB1D5C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches_individuelles ADD CONSTRAINT FK_106FBB1DDA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches_fratries ADD CONSTRAINT FK_6A2AD6E35C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planches_fratries ADD CONSTRAINT FK_6A2AD6E3DA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON DELETE CASCADE
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
            DROP TABLE prise_de_vue_planche
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prise_de_vue_planche_fratrie
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_planche (prise_de_vue_id INT NOT NULL, planche_id INT NOT NULL, INDEX IDX_1F5DCA2FDA8652A8 (planche_id), INDEX IDX_1F5DCA2F5C08B7F7 (prise_de_vue_id), PRIMARY KEY(prise_de_vue_id, planche_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prise_de_vue_planche_fratrie (prise_de_vue_id INT NOT NULL, planche_id INT NOT NULL, INDEX IDX_C3AED062DA8652A8 (planche_id), INDEX IDX_C3AED0625C08B7F7 (prise_de_vue_id), PRIMARY KEY(prise_de_vue_id, planche_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche ADD CONSTRAINT FK_1F5DCA2F5C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche ADD CONSTRAINT FK_1F5DCA2FDA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche_fratrie ADD CONSTRAINT FK_C3AED0625C08B7F7 FOREIGN KEY (prise_de_vue_id) REFERENCES prise_de_vue (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue_planche_fratrie ADD CONSTRAINT FK_C3AED062DA8652A8 FOREIGN KEY (planche_id) REFERENCES planche (id) ON UPDATE NO ACTION ON DELETE CASCADE
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
    }
}
