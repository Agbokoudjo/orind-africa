<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251028164244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity_log (id SERIAL NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_context JSONB NOT NULL, ip_address VARCHAR(45) NOT NULL, action VARCHAR(100) NOT NULL, method VARCHAR(20) NOT NULL, route VARCHAR(255) NOT NULL, context JSONB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FD06F6478B8E8428 ON activity_log (created_at)');
        $this->addSql('CREATE INDEX IDX_FD06F64722FFD58C ON activity_log (ip_address)');
        $this->addSql('COMMENT ON COLUMN activity_log.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE activity_log');
    }
}
