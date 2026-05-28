<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528144516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Correction de created_at
        if (!$schema->getTable('commande')->hasColumn('created_at')) {
            $this->addSql('ALTER TABLE commande ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        } else {
            $this->addSql("UPDATE commande SET created_at = CURRENT_TIMESTAMP WHERE created_at = '0000-00-00 00:00:00'");
            $this->addSql('ALTER TABLE commande CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        }

        // Modifications commande_item
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FD9AF8E3A3');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FDAABEFE2C');

        $this->addSql('DROP INDEX UNIQ_747724FD9AF8E3A3 ON commande_item');
        $this->addSql('DROP INDEX UNIQ_747724FDAABEFE2C ON commande_item');

        $this->addSql('ALTER TABLE commande_item ADD id_commande INT DEFAULT NULL, DROP id_commande_id, DROP id_produit_id');

        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FD3E314AE8 FOREIGN KEY (id_commande) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FDBF396750 FOREIGN KEY (id) REFERENCES produit (id)');

        $this->addSql('CREATE INDEX IDX_747724FD3E314AE8 ON commande_item (id_commande)');

        // Correction description produit
        $this->addSql('ALTER TABLE produit CHANGE description description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');

        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FD3E314AE8');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FDBF396750');

        $this->addSql('DROP INDEX IDX_747724FD3E314AE8 ON commande_item');

        $this->addSql('ALTER TABLE commande_item ADD id_produit_id INT DEFAULT NULL, CHANGE id_commande id_commande_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FD9AF8E3A3 FOREIGN KEY (id_commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FDAABEFE2C FOREIGN KEY (id_produit_id) REFERENCES produit (id)');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_747724FD9AF8E3A3 ON commande_item (id_commande_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_747724FDAABEFE2C ON commande_item (id_produit_id)');

        $this->addSql('ALTER TABLE produit CHANGE description description VARCHAR(255) NOT NULL');
    }
}