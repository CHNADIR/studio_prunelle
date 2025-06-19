<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250622AddLastLogin extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne last_login à la table user';
    }

    public function up(Schema $schema): void
    {
        // Vérifier si la colonne existe déjà avant de l'ajouter
        $this->addSql('ALTER TABLE user ADD last_login DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP last_login');
    }
}