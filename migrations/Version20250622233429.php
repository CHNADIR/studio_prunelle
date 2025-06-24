<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250622233429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE planche ADD type_vente VARCHAR(255) DEFAULT NULL, ADD installation VARCHAR(255) DEFAULT NULL, ADD pochette_plastique VARCHAR(255) DEFAULT NULL, ADD cartonnage VARCHAR(255) DEFAULT NULL, ADD bon_commande VARCHAR(255) DEFAULT NULL, ADD commentaire LONGTEXT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE planche DROP type_vente, DROP installation, DROP pochette_plastique, DROP cartonnage, DROP bon_commande, DROP commentaire
        SQL);
    }
}
