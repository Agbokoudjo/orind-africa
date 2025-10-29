<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251024164533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sonata_abstract_user ADD token_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE sonata_abstract_user ADD is_email_verified BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE sonata_abstract_user ADD email_verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE sonata_abstract_user ALTER confirmation_token TYPE VARCHAR(255)');
        $this->addSql('COMMENT ON COLUMN sonata_abstract_user.email_verified_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE sonata_abstract_user DROP token_requested_at');
        $this->addSql('ALTER TABLE sonata_abstract_user DROP is_email_verified');
        $this->addSql('ALTER TABLE sonata_abstract_user DROP email_verified_at');
        $this->addSql('ALTER TABLE sonata_abstract_user ALTER confirmation_token TYPE VARCHAR(180)');
    }
}
