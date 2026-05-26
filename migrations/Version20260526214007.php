<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260526214007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id_notif INT AUTO_INCREMENT NOT NULL, recipient_username VARCHAR(30) NOT NULL, recipient_role VARCHAR(10) NOT NULL, type VARCHAR(30) NOT NULL, title VARCHAR(120) NOT NULL, body LONGTEXT DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, actor_username VARCHAR(30) DEFAULT NULL, related_id INT DEFAULT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id_notif)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE notification');
    }
}
