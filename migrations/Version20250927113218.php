<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250927113218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE group_action DROP CONSTRAINT FK_944A5442115F0EE5');
        $this->addSql('ALTER TABLE group_action ADD current_manager_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE group_action ALTER domain_id DROP NOT NULL');
        $this->addSql('ALTER TABLE group_action ADD CONSTRAINT FK_944A5442E4773CD5 FOREIGN KEY (current_manager_group_id) REFERENCES sonata_abstract_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE group_action ADD CONSTRAINT FK_944A5442115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain_action (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_944A5442E4773CD5 ON group_action (current_manager_group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE group_action DROP CONSTRAINT FK_944A5442E4773CD5');
        $this->addSql('ALTER TABLE group_action DROP CONSTRAINT fk_944a5442115f0ee5');
        $this->addSql('DROP INDEX IDX_944A5442E4773CD5');
        $this->addSql('ALTER TABLE group_action DROP current_manager_group_id');
        $this->addSql('ALTER TABLE group_action ALTER domain_id SET NOT NULL');
        $this->addSql('ALTER TABLE group_action ADD CONSTRAINT fk_944a5442115f0ee5 FOREIGN KEY (domain_id) REFERENCES domain_action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
