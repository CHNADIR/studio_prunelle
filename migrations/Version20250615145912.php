<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250615145912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE planche ADD description_contenu LONGTEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue ADD frequence VARCHAR(255) DEFAULT NULL, ADD base_de_donnee_utilisee VARCHAR(255) DEFAULT NULL, ADD jour_decharge VARCHAR(255) DEFAULT NULL, ADD endroit_installation VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE prise_de_vue DROP frequence, DROP base_de_donnee_utilisee, DROP jour_decharge, DROP endroit_installation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planche DROP description_contenu
        SQL);
    }
}
