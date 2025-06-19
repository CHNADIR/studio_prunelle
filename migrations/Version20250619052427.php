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
        return 'Création initiale des tables User, Ecole et PriseDeVue';
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
        
        // Création de la table prise_de_vue
        $this->addSql('CREATE TABLE prise_de_vue (
            id INT AUTO_INCREMENT NOT NULL,
            ecole_id INT NOT NULL,
            photographe_id INT NOT NULL,
            date DATE NOT NULL,
            nb_eleves INT NOT NULL,
            classes VARCHAR(255) DEFAULT NULL,
            commentaire LONGTEXT DEFAULT NULL,
            INDEX IDX_XXXXXXX_ecole_id (ecole_id),
            INDEX IDX_XXXXXXX_photographe_id (photographe_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Contraintes de clé étrangère
        $this->addSql('ALTER TABLE prise_de_vue ADD CONSTRAINT FK_XXXXXXX_ecole_id FOREIGN KEY (ecole_id) REFERENCES ecole (id)');
        $this->addSql('ALTER TABLE prise_de_vue ADD CONSTRAINT FK_XXXXXXX_photographe_id FOREIGN KEY (photographe_id) REFERENCES user (id)');
        
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
        $this->addSql('DROP TABLE prise_de_vue');
        $this->addSql('DROP TABLE ecole');
        $this->addSql('DROP TABLE user');
    }
}
