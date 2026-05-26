<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260525161009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la date de création du vendeur';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deal_request ADD status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE vendeur ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deal_request ADD status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE vendeur ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');

    }
}
