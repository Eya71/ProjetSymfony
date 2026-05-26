<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260525230935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(50) NOT NULL, source VARCHAR(255) NOT NULL, total NUMERIC(10, 2) NOT NULL, id_demande_id INT DEFAULT NULL, vendeur_id INT NOT NULL, client_id INT NOT NULL, INDEX IDX_6EEAA67D2563DECF (id_demande_id), INDEX IDX_6EEAA67D858C065E (vendeur_id), INDEX IDX_6EEAA67D19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE commande_item (id INT AUTO_INCREMENT NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, quantite INT NOT NULL, sous_total NUMERIC(10, 2) NOT NULL, image_path VARCHAR(255) NOT NULL, id_commande_id INT DEFAULT NULL, id_produit_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_747724FD9AF8E3A3 (id_commande_id), UNIQUE INDEX UNIQ_747724FDAABEFE2C (id_produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D2563DECF FOREIGN KEY (id_demande_id) REFERENCES demande (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FD9AF8E3A3 FOREIGN KEY (id_commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FDAABEFE2C FOREIGN KEY (id_produit_id) REFERENCES produit (id)');
        $this->addSql('DROP TABLE commandes');
        $this->addSql('ALTER TABLE client ADD id INT AUTO_INCREMENT NOT NULL, ADD id_photo VARCHAR(200) NOT NULL, ADD pwd VARCHAR(100) NOT NULL, DROP idphoto, DROP password, DROP created_at, CHANGE username username VARCHAR(50) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455F85E0677 ON client (username)');
        $this->addSql('ALTER TABLE deal_request DROP FOREIGN KEY fk_deal_client');
        $this->addSql('ALTER TABLE deal_request DROP FOREIGN KEY fk_deal_demande');
        $this->addSql('ALTER TABLE deal_request DROP FOREIGN KEY fk_deal_vendeur');
        $this->addSql('DROP INDEX fk_deal_demande ON deal_request');
        $this->addSql('DROP INDEX fk_deal_client ON deal_request');
        $this->addSql('DROP INDEX fk_deal_vendeur ON deal_request');
        $this->addSql('ALTER TABLE deal_request ADD id_demande_id INT DEFAULT NULL, ADD client_username_id INT DEFAULT NULL, ADD vendeur_username_id INT DEFAULT NULL, DROP id_demande, DROP client_username, DROP vendeur_username, CHANGE message message LONGTEXT NOT NULL, CHANGE status status VARCHAR(20) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE client_seen_at client_seen_at DATETIME NOT NULL, CHANGE vendeur_seen_at vendeur_seen_at DATETIME NOT NULL, CHANGE id_deal id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE deal_request ADD CONSTRAINT FK_DF8D07172563DECF FOREIGN KEY (id_demande_id) REFERENCES demande (id)');
        $this->addSql('ALTER TABLE deal_request ADD CONSTRAINT FK_DF8D0717EC3D4553 FOREIGN KEY (client_username_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE deal_request ADD CONSTRAINT FK_DF8D0717EE595F13 FOREIGN KEY (vendeur_username_id) REFERENCES vendeur (id)');
        $this->addSql('CREATE INDEX IDX_DF8D07172563DECF ON deal_request (id_demande_id)');
        $this->addSql('CREATE INDEX IDX_DF8D0717EC3D4553 ON deal_request (client_username_id)');
        $this->addSql('CREATE INDEX IDX_DF8D0717EE595F13 ON deal_request (vendeur_username_id)');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY fk_demande_client');
        $this->addSql('DROP INDEX fk_demande_client ON demande');
        $this->addSql('ALTER TABLE demande ADD descrption VARCHAR(255) NOT NULL, ADD username_id INT DEFAULT NULL, DROP description, DROP username, DROP created_at, CHANGE id_photo id_photo VARCHAR(255) NOT NULL, CHANGE etat etat VARCHAR(20) NOT NULL, CHANGE id_demande id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5ED766068 FOREIGN KEY (username_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_2694D7A5ED766068 ON demande (username_id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY fk_message_deal');
        $this->addSql('DROP INDEX fk_message_deal ON message');
        $this->addSql('ALTER TABLE message ADD id_deal_id INT DEFAULT NULL, DROP id_deal, CHANGE contenu contenu LONGTEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE is_read is_read TINYINT(1) NOT NULL, CHANGE id_message id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F28938A75 FOREIGN KEY (id_deal_id) REFERENCES deal_request (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307F28938A75 ON message (id_deal_id)');
        $this->addSql('ALTER TABLE panier CHANGE username username VARCHAR(255) NOT NULL, CHANGE quantite quantite INT NOT NULL, CHANGE date_ajout date_ajout TIME NOT NULL, CHANGE id_panier id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY fk_produit_vendeur');
        $this->addSql('DROP INDEX fk_produit_vendeur ON produit');
        $this->addSql('ALTER TABLE produit ADD decription VARCHAR(255) NOT NULL, ADD vendeur_username_id INT DEFAULT NULL, DROP vendeur_username, DROP description, DROP created_at, CHANGE nom_produit nom_produit VARCHAR(255) NOT NULL, CHANGE quantite quantite INT NOT NULL, CHANGE categorie categorie VARCHAR(255) NOT NULL, CHANGE image_path image_path VARCHAR(255) NOT NULL, CHANGE id_produit id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27EE595F13 FOREIGN KEY (vendeur_username_id) REFERENCES vendeur (id)');
        $this->addSql('CREATE INDEX IDX_29A5EC27EE595F13 ON produit (vendeur_username_id)');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY fk_review_vendeur');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY fk_review_client');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY fk_review_deal');
        $this->addSql('DROP INDEX fk_review_vendeur ON review');
        $this->addSql('DROP INDEX fk_review_deal ON review');
        $this->addSql('DROP INDEX fk_review_client ON review');
        $this->addSql('ALTER TABLE review ADD id_deal_id INT DEFAULT NULL, ADD client_username_id INT DEFAULT NULL, ADD vendeur_username_id INT DEFAULT NULL, DROP id_deal, DROP client_username, DROP vendeur_username, CHANGE rating rating INT NOT NULL, CHANGE commentaire commentaire LONGTEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE id_review id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C628938A75 FOREIGN KEY (id_deal_id) REFERENCES deal_request (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6EC3D4553 FOREIGN KEY (client_username_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6EE595F13 FOREIGN KEY (vendeur_username_id) REFERENCES vendeur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_794381C628938A75 ON review (id_deal_id)');
        $this->addSql('CREATE INDEX IDX_794381C6EC3D4553 ON review (client_username_id)');
        $this->addSql('CREATE INDEX IDX_794381C6EE595F13 ON review (vendeur_username_id)');
        $this->addSql('ALTER TABLE vendeur ADD id INT AUTO_INCREMENT NOT NULL, ADD id_photo VARCHAR(200) NOT NULL, ADD pwd VARCHAR(100) NOT NULL, DROP idphoto, DROP password, CHANGE username username VARCHAR(50) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7AF49996F85E0677 ON vendeur (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commandes (id INT AUTO_INCREMENT NOT NULL, id_demande INT DEFAULT NULL, vendeur VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, client VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D2563DECF');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D858C065E');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D19EB6921');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FD9AF8E3A3');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FDAABEFE2C');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE commande_item');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP INDEX UNIQ_C7440455F85E0677 ON client');
        $this->addSql('ALTER TABLE client ADD idphoto VARCHAR(255) DEFAULT NULL, ADD password VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP id, DROP id_photo, DROP pwd, CHANGE username username VARCHAR(30) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (username)');
        $this->addSql('ALTER TABLE deal_request DROP FOREIGN KEY FK_DF8D07172563DECF');
        $this->addSql('ALTER TABLE deal_request DROP FOREIGN KEY FK_DF8D0717EC3D4553');
        $this->addSql('ALTER TABLE deal_request DROP FOREIGN KEY FK_DF8D0717EE595F13');
        $this->addSql('DROP INDEX IDX_DF8D07172563DECF ON deal_request');
        $this->addSql('DROP INDEX IDX_DF8D0717EC3D4553 ON deal_request');
        $this->addSql('DROP INDEX IDX_DF8D0717EE595F13 ON deal_request');
        $this->addSql('ALTER TABLE deal_request ADD id_demande INT NOT NULL, ADD client_username VARCHAR(30) NOT NULL, ADD vendeur_username VARCHAR(30) NOT NULL, DROP id_demande_id, DROP client_username_id, DROP vendeur_username_id, CHANGE message message TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE client_seen_at client_seen_at DATETIME DEFAULT NULL, CHANGE vendeur_seen_at vendeur_seen_at DATETIME DEFAULT NULL, CHANGE status status VARCHAR(20) DEFAULT \'en attente\' NOT NULL, CHANGE id id_deal INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_deal)');
        $this->addSql('ALTER TABLE deal_request ADD CONSTRAINT fk_deal_client FOREIGN KEY (client_username) REFERENCES client (username) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE deal_request ADD CONSTRAINT fk_deal_demande FOREIGN KEY (id_demande) REFERENCES demande (id_demande) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE deal_request ADD CONSTRAINT fk_deal_vendeur FOREIGN KEY (vendeur_username) REFERENCES vendeur (username) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_deal_demande ON deal_request (id_demande)');
        $this->addSql('CREATE INDEX fk_deal_client ON deal_request (client_username)');
        $this->addSql('CREATE INDEX fk_deal_vendeur ON deal_request (vendeur_username)');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5ED766068');
        $this->addSql('DROP INDEX IDX_2694D7A5ED766068 ON demande');
        $this->addSql('ALTER TABLE demande ADD description TEXT NOT NULL, ADD username VARCHAR(30) NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP descrption, DROP username_id, CHANGE id_photo id_photo VARCHAR(255) DEFAULT NULL, CHANGE etat etat VARCHAR(20) DEFAULT \'en attente\' NOT NULL, CHANGE id id_demande INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_demande)');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT fk_demande_client FOREIGN KEY (username) REFERENCES client (username) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_demande_client ON demande (username)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F28938A75');
        $this->addSql('DROP INDEX IDX_B6BD307F28938A75 ON message');
        $this->addSql('ALTER TABLE message ADD id_deal INT NOT NULL, DROP id_deal_id, CHANGE contenu contenu TEXT NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE is_read is_read TINYINT(1) DEFAULT 0 NOT NULL, CHANGE id id_message INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_message)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT fk_message_deal FOREIGN KEY (id_deal) REFERENCES deal_request (id_deal) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_message_deal ON message (id_deal)');
        $this->addSql('ALTER TABLE panier CHANGE username username VARCHAR(30) NOT NULL, CHANGE quantite quantite INT DEFAULT 1 NOT NULL, CHANGE date_ajout date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE id id_panier INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_panier)');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27EE595F13');
        $this->addSql('DROP INDEX IDX_29A5EC27EE595F13 ON produit');
        $this->addSql('ALTER TABLE produit ADD vendeur_username VARCHAR(30) NOT NULL, ADD description TEXT DEFAULT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP decription, DROP vendeur_username_id, CHANGE nom_produit nom_produit VARCHAR(80) NOT NULL, CHANGE quantite quantite INT DEFAULT 0 NOT NULL, CHANGE categorie categorie VARCHAR(30) NOT NULL, CHANGE image_path image_path VARCHAR(255) DEFAULT NULL, CHANGE id id_produit INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_produit)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT fk_produit_vendeur FOREIGN KEY (vendeur_username) REFERENCES vendeur (username) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_produit_vendeur ON produit (vendeur_username)');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C628938A75');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6EC3D4553');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6EE595F13');
        $this->addSql('DROP INDEX UNIQ_794381C628938A75 ON review');
        $this->addSql('DROP INDEX IDX_794381C6EC3D4553 ON review');
        $this->addSql('DROP INDEX IDX_794381C6EE595F13 ON review');
        $this->addSql('ALTER TABLE review ADD id_deal INT NOT NULL, ADD client_username VARCHAR(30) NOT NULL, ADD vendeur_username VARCHAR(30) NOT NULL, DROP id_deal_id, DROP client_username_id, DROP vendeur_username_id, CHANGE rating rating TINYINT(1) NOT NULL, CHANGE commentaire commentaire TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE id id_review INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_review)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_review_vendeur FOREIGN KEY (vendeur_username) REFERENCES vendeur (username) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_review_client FOREIGN KEY (client_username) REFERENCES client (username) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_review_deal FOREIGN KEY (id_deal) REFERENCES deal_request (id_deal) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_review_vendeur ON review (vendeur_username)');
        $this->addSql('CREATE INDEX fk_review_deal ON review (id_deal)');
        $this->addSql('CREATE INDEX fk_review_client ON review (client_username)');
        $this->addSql('DROP INDEX UNIQ_7AF49996F85E0677 ON vendeur');
        $this->addSql('ALTER TABLE vendeur ADD idphoto VARCHAR(255) DEFAULT NULL, ADD password VARCHAR(255) NOT NULL, DROP id, DROP id_photo, DROP pwd, CHANGE username username VARCHAR(30) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (username)');
    }
}
