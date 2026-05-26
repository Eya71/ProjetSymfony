<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260525215025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deal_request DROP INDEX UNIQ_DF8D0717EE595F13, ADD INDEX IDX_DF8D0717EE595F13 (vendeur_username_id)');
        $this->addSql('ALTER TABLE deal_request DROP INDEX UNIQ_DF8D0717EC3D4553, ADD INDEX IDX_DF8D0717EC3D4553 (client_username_id)');
        $this->addSql('ALTER TABLE deal_request ADD status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE vendeur ADD created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deal_request DROP INDEX IDX_DF8D0717EC3D4553, ADD UNIQUE INDEX UNIQ_DF8D0717EC3D4553 (client_username_id)');
        $this->addSql('ALTER TABLE deal_request DROP INDEX IDX_DF8D0717EE595F13, ADD UNIQUE INDEX UNIQ_DF8D0717EE595F13 (vendeur_username_id)');
        $this->addSql('ALTER TABLE deal_request DROP status');
        $this->addSql('ALTER TABLE vendeur DROP created_at');
    }
}
