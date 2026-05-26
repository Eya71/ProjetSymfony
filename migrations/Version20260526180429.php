<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260526180429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FD9AF8E3A3');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FDAABEFE2C');
        $this->addSql('DROP INDEX UNIQ_747724FD9AF8E3A3 ON commande_item');
        $this->addSql('DROP INDEX UNIQ_747724FDAABEFE2C ON commande_item');
        $this->addSql('ALTER TABLE commande_item ADD id_commande INT DEFAULT NULL, DROP id_commande_id, DROP id_produit_id');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FD3E314AE8 FOREIGN KEY (id_commande) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FDBF396750 FOREIGN KEY (id) REFERENCES produit (id)');
        $this->addSql('CREATE INDEX IDX_747724FD3E314AE8 ON commande_item (id_commande)');
        $this->addSql('ALTER TABLE deal_request DROP INDEX UNIQ_DF8D0717EC3D4553, ADD INDEX IDX_DF8D0717EC3D4553 (client_username_id)');
        $this->addSql('ALTER TABLE deal_request DROP INDEX UNIQ_DF8D0717EE595F13, ADD INDEX IDX_DF8D0717EE595F13 (vendeur_username_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP created_at');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FD3E314AE8');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FDBF396750');
        $this->addSql('DROP INDEX IDX_747724FD3E314AE8 ON commande_item');
        $this->addSql('ALTER TABLE commande_item ADD id_produit_id INT DEFAULT NULL, CHANGE id_commande id_commande_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FD9AF8E3A3 FOREIGN KEY (id_commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FDAABEFE2C FOREIGN KEY (id_produit_id) REFERENCES produit (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_747724FD9AF8E3A3 ON commande_item (id_commande_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_747724FDAABEFE2C ON commande_item (id_produit_id)');
        $this->addSql('ALTER TABLE deal_request DROP INDEX IDX_DF8D0717EC3D4553, ADD UNIQUE INDEX UNIQ_DF8D0717EC3D4553 (client_username_id)');
        $this->addSql('ALTER TABLE deal_request DROP INDEX IDX_DF8D0717EE595F13, ADD UNIQUE INDEX UNIQ_DF8D0717EE595F13 (vendeur_username_id)');
    }
}
