<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250623121048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ecole CHANGE created_at created_at DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9786AAC77153098 ON ecole (code)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_planche_active_libelle ON planche
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_pochette_fratrie_active_libelle ON pochette_fratrie
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_pochette_indiv_active_libelle ON pochette_indiv
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_prise_de_vue_date_pdv ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_prise_de_vue_photographe_date ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_prise_de_vue_ecole_date ON prise_de_vue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_theme_active_libelle ON theme
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theme ADD description LONGTEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_type_prise_active_libelle ON type_prise
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_type_vente_active_libelle ON type_vente
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type_vente ADD description LONGTEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_user_actif ON user
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_9786AAC77153098 ON ecole
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ecole CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theme DROP description
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_theme_active_libelle ON theme (active, libelle)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_planche_active_libelle ON planche (active, libelle)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_type_prise_active_libelle ON type_prise (active, libelle)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_prise_de_vue_date_pdv ON prise_de_vue (date_pdv)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_prise_de_vue_photographe_date ON prise_de_vue (photographe_id, date_pdv)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_prise_de_vue_ecole_date ON prise_de_vue (ecole_id, date_pdv)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type_vente DROP description
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_type_vente_active_libelle ON type_vente (active, libelle)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_pochette_indiv_active_libelle ON pochette_indiv (active, libelle)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_pochette_fratrie_active_libelle ON pochette_fratrie (active, libelle)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_user_actif ON user (actif)
        SQL);
    }
}
