<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250924154329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE permission_role ADD created_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE permission_role ADD CONSTRAINT FK_6A711CAB03A8386 FOREIGN KEY (created_by_id) REFERENCES sonata_abstract_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6A711CAB03A8386 ON permission_role (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE permission_role DROP CONSTRAINT FK_6A711CAB03A8386');
        $this->addSql('DROP INDEX IDX_6A711CAB03A8386');
        $this->addSql('ALTER TABLE permission_role DROP created_by_id');
    }
}
