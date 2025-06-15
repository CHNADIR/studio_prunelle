<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250615150322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adjust schema, description_contenu might already exist'; // Mettez une description pertinente
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE planche ADD description_contenu TEXT DEFAULT NULL'); // Commentez ou supprimez cette ligne si elle cause l'erreur

        // Laissez les autres instructions SQL si elles sont correctes, par exemple :
        // $this->addSql('ALTER TABLE prise_de_vue ADD frequence VARCHAR(255) DEFAULT NULL');
        // $this->addSql('ALTER TABLE prise_de_vue ADD base_de_donnee_utilisee VARCHAR(255) DEFAULT NULL');
        // ... etc pour les autres nouveaux champs de PriseDeVue
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE planche DROP description_contenu'); // Commentez ou supprimez également la contrepartie ici

        // Laissez les autres instructions SQL si elles sont correctes
        // $this->addSql('ALTER TABLE prise_de_vue DROP frequence');
        // ... etc.
    }
}
