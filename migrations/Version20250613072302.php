<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250613072302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9786AAC77153098 ON ecole (code)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planche ADD image_filename VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue CHANGE theme_id theme_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9775E7086C6E55B5 ON theme (nom)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8E06B77F6C6E55B5 ON type_prise (nom)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8686ADBB6C6E55B5 ON type_vente (nom)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE planche DROP image_filename
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_9775E7086C6E55B5 ON theme
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8E06B77F6C6E55B5 ON type_prise
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue CHANGE theme_id theme_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8686ADBB6C6E55B5 ON type_vente
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_9786AAC77153098 ON ecole
        SQL);
    }
}
