<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260523215458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, adresse VARCHAR(100) NOT NULL, num_tel VARCHAR(20) NOT NULL, id_photo VARCHAR(200) NOT NULL, pwd VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_C7440455F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(50) NOT NULL, source VARCHAR(255) NOT NULL, total NUMERIC(10, 2) NOT NULL, id_demande_id INT DEFAULT NULL, vendeur_id INT NOT NULL, client_id INT NOT NULL, INDEX IDX_6EEAA67D2563DECF (id_demande_id), INDEX IDX_6EEAA67D858C065E (vendeur_id), INDEX IDX_6EEAA67D19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE commande_item (id INT AUTO_INCREMENT NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, quantite INT NOT NULL, sous_total NUMERIC(10, 2) NOT NULL, image_path VARCHAR(255) NOT NULL, id_commande_id INT DEFAULT NULL, id_produit_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_747724FD9AF8E3A3 (id_commande_id), UNIQUE INDEX UNIQ_747724FDAABEFE2C (id_produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE deal_request (id INT AUTO_INCREMENT NOT NULL, prix_propose NUMERIC(10, 2) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, client_seen_at DATETIME NOT NULL, vendeur_seen_at DATETIME NOT NULL, id_demande_id INT DEFAULT NULL, client_username_id INT DEFAULT NULL, vendeur_username_id INT DEFAULT NULL, INDEX IDX_DF8D07172563DECF (id_demande_id), UNIQUE INDEX UNIQ_DF8D0717EC3D4553 (client_username_id), UNIQUE INDEX UNIQ_DF8D0717EE595F13 (vendeur_username_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, nom_produit VARCHAR(80) NOT NULL, prix NUMERIC(10, 2) NOT NULL, lien_produit VARCHAR(255) NOT NULL, descrption VARCHAR(255) NOT NULL, categorie VARCHAR(30) NOT NULL, id_photo VARCHAR(255) NOT NULL, etat VARCHAR(20) NOT NULL, username_id INT DEFAULT NULL, INDEX IDX_2694D7A5ED766068 (username_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, sender_username VARCHAR(30) NOT NULL, receiver_username VARCHAR(30) NOT NULL, contenu LONGTEXT NOT NULL, created_at DATETIME NOT NULL, is_read TINYINT(1) NOT NULL, id_deal_id INT DEFAULT NULL, INDEX IDX_B6BD307F28938A75 (id_deal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE panier (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, id_produit INT NOT NULL, quantite INT NOT NULL, date_ajout TIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, nom_produit VARCHAR(255) NOT NULL, prix NUMERIC(10, 2) NOT NULL, quantite INT NOT NULL, categorie VARCHAR(255) NOT NULL, decription VARCHAR(255) NOT NULL, image_path VARCHAR(255) NOT NULL, vendeur_username_id INT DEFAULT NULL, INDEX IDX_29A5EC27EE595F13 (vendeur_username_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, rating INT NOT NULL, commentaire LONGTEXT NOT NULL, created_at DATETIME NOT NULL, id_deal_id INT DEFAULT NULL, client_username_id INT DEFAULT NULL, vendeur_username_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_794381C628938A75 (id_deal_id), INDEX IDX_794381C6EC3D4553 (client_username_id), INDEX IDX_794381C6EE595F13 (vendeur_username_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vendeur (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, adresse VARCHAR(100) NOT NULL, num_tel VARCHAR(20) NOT NULL, id_photo VARCHAR(200) NOT NULL, pwd VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_7AF49996F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D2563DECF FOREIGN KEY (id_demande_id) REFERENCES demande (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FD9AF8E3A3 FOREIGN KEY (id_commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FDAABEFE2C FOREIGN KEY (id_produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE deal_request ADD CONSTRAINT FK_DF8D07172563DECF FOREIGN KEY (id_demande_id) REFERENCES demande (id)');
        $this->addSql('ALTER TABLE deal_request ADD CONSTRAINT FK_DF8D0717EC3D4553 FOREIGN KEY (client_username_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE deal_request ADD CONSTRAINT FK_DF8D0717EE595F13 FOREIGN KEY (vendeur_username_id) REFERENCES vendeur (id)');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5ED766068 FOREIGN KEY (username_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F28938A75 FOREIGN KEY (id_deal_id) REFERENCES deal_request (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27EE595F13 FOREIGN KEY (vendeur_username_id) REFERENCES vendeur (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C628938A75 FOREIGN KEY (id_deal_id) REFERENCES deal_request (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6EC3D4553 FOREIGN KEY (client_username_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6EE595F13 FOREIGN KEY (vendeur_username_id) REFERENCES vendeur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D2563DECF');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D858C065E');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D19EB6921');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FD9AF8E3A3');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FDAABEFE2C');
        $this->addSql('ALTER TABLE deal_request DROP FOREIGN KEY FK_DF8D07172563DECF');
        $this->addSql('ALTER TABLE deal_request DROP FOREIGN KEY FK_DF8D0717EC3D4553');
        $this->addSql('ALTER TABLE deal_request DROP FOREIGN KEY FK_DF8D0717EE595F13');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5ED766068');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F28938A75');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27EE595F13');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C628938A75');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6EC3D4553');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6EE595F13');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE commande_item');
        $this->addSql('DROP TABLE deal_request');
        $this->addSql('DROP TABLE demande');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE panier');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE vendeur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
