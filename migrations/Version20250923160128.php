<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923160128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE permission_role (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, description TEXT NOT NULL, context VARCHAR(200) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A711CA5E237E06 ON permission_role (name)');
        $this->addSql('CREATE TABLE user_permission_role (id SERIAL NOT NULL, roles_id INT NOT NULL, user_type VARCHAR(50) NOT NULL, user_id INT NOT NULL, assigned_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, scope VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_147B7D4538C751C4 ON user_permission_role (roles_id)');
        $this->addSql('COMMENT ON COLUMN user_permission_role.assigned_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user_permission_role ADD CONSTRAINT FK_147B7D4538C751C4 FOREIGN KEY (roles_id) REFERENCES permission_role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE logger_login_user ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN logger_login_user.created_at IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_permission_role DROP CONSTRAINT FK_147B7D4538C751C4');
        $this->addSql('DROP TABLE permission_role');
        $this->addSql('DROP TABLE user_permission_role');
        $this->addSql('ALTER TABLE logger_login_user ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN logger_login_user.created_at IS \'(DC2Type:datetime_immutable)\'');
    }
}
