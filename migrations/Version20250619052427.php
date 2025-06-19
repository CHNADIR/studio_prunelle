<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250619052427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création initiale des tables User et Ecole';
    }

    public function up(Schema $schema): void
    {
        // Création de la table user
        $this->addSql('CREATE TABLE user (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            nom VARCHAR(100) NOT NULL,
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Création de la table ecole
        $this->addSql('CREATE TABLE ecole (
            id INT AUTO_INCREMENT NOT NULL,
            code VARCHAR(5) NOT NULL,
            nom VARCHAR(200) NOT NULL,
            genre VARCHAR(50) NOT NULL,
            adresse VARCHAR(255) NOT NULL,
            ville VARCHAR(100) NOT NULL,
            code_postal VARCHAR(10) NOT NULL,
            telephone VARCHAR(20) NOT NULL,
            email VARCHAR(180) DEFAULT NULL,
            active TINYINT(1) NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Ajout de la table doctrine_migration_versions si elle n'existe pas déjà
        $this->addSql('CREATE TABLE IF NOT EXISTS doctrine_migration_versions (
            version VARCHAR(191) NOT NULL,
            executed_at DATETIME DEFAULT NULL,
            execution_time INT DEFAULT NULL,
            PRIMARY KEY(version)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // Suppression des tables dans l'ordre inverse
        $this->addSql('DROP TABLE ecole');
        $this->addSql('DROP TABLE user');
    }
}
