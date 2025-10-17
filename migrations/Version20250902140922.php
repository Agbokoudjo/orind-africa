<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250902140922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE minister_roles_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sonata_admin_users_id_seq CASCADE');
        $this->addSql('CREATE TABLE domain_action (id SERIAL NOT NULL, owner_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_28AF805B5E237E06 ON domain_action (name)');
        $this->addSql('CREATE INDEX IDX_28AF805B7E3C61F9 ON domain_action (owner_id)');
        $this->addSql('CREATE TABLE group_action (id SERIAL NOT NULL, domain_id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, created_by_username VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_944A54425E237E06 ON group_action (name)');
        $this->addSql('CREATE INDEX IDX_944A5442115F0EE5 ON group_action (domain_id)');
        $this->addSql('CREATE TABLE logger_login_user (id SERIAL NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, user_class VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_login_ip VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN logger_login_user.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE sonata_abstract_user (id SERIAL NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(200) NOT NULL, roles JSON NOT NULL, slug VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) DEFAULT NULL, email_canonical VARCHAR(200) DEFAULT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, avatar_name VARCHAR(255) DEFAULT NULL, document_name VARCHAR(255) DEFAULT NULL, profile VARCHAR(200) DEFAULT NULL, phone VARCHAR(80) DEFAULT NULL, country VARCHAR(200) DEFAULT NULL, type VARCHAR(255) NOT NULL, skills JSON DEFAULT NULL, interests JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD063A2DF85E0677 ON sonata_abstract_user (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD063A2DE7927C74 ON sonata_abstract_user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD063A2D92FC23A8 ON sonata_abstract_user (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD063A2DA0D96FBF ON sonata_abstract_user (email_canonical)');
        $this->addSql('COMMENT ON COLUMN sonata_abstract_user.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN sonata_abstract_user.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('CREATE TABLE member_user_group_action_entity (member_user_id INT NOT NULL, group_action_entity_id INT NOT NULL, PRIMARY KEY(member_user_id, group_action_entity_id))');
        $this->addSql('CREATE INDEX IDX_8B6DA1DF189A6401 ON member_user_group_action_entity (member_user_id)');
        $this->addSql('CREATE INDEX IDX_8B6DA1DFDA9297AD ON member_user_group_action_entity (group_action_entity_id)');
        $this->addSql('ALTER TABLE domain_action ADD CONSTRAINT FK_28AF805B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES sonata_abstract_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE group_action ADD CONSTRAINT FK_944A5442115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain_action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE member_user_group_action_entity ADD CONSTRAINT FK_8B6DA1DF189A6401 FOREIGN KEY (member_user_id) REFERENCES sonata_abstract_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE member_user_group_action_entity ADD CONSTRAINT FK_8B6DA1DFDA9297AD FOREIGN KEY (group_action_entity_id) REFERENCES group_action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE minister_roles');
        $this->addSql('DROP TABLE sonata_admin_users');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE minister_roles_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sonata_admin_users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE minister_roles (id SERIAL NOT NULL, role_code VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_5709e1cfc9aa420c ON minister_roles (role_code)');
        $this->addSql('CREATE TABLE sonata_admin_users (id SERIAL NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(200) NOT NULL, roles JSON NOT NULL, slug VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) DEFAULT NULL, email_canonical VARCHAR(200) DEFAULT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, photoname VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_a2f3129c92fc23a8 ON sonata_admin_users (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX uniq_a2f3129ca0d96fbf ON sonata_admin_users (email_canonical)');
        $this->addSql('CREATE UNIQUE INDEX uniq_a2f3129ce7927c74 ON sonata_admin_users (email)');
        $this->addSql('CREATE UNIQUE INDEX uniq_a2f3129cf85e0677 ON sonata_admin_users (username)');
        $this->addSql('COMMENT ON COLUMN sonata_admin_users.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE domain_action DROP CONSTRAINT FK_28AF805B7E3C61F9');
        $this->addSql('ALTER TABLE group_action DROP CONSTRAINT FK_944A5442115F0EE5');
        $this->addSql('ALTER TABLE member_user_group_action_entity DROP CONSTRAINT FK_8B6DA1DF189A6401');
        $this->addSql('ALTER TABLE member_user_group_action_entity DROP CONSTRAINT FK_8B6DA1DFDA9297AD');
        $this->addSql('DROP TABLE domain_action');
        $this->addSql('DROP TABLE group_action');
        $this->addSql('DROP TABLE logger_login_user');
        $this->addSql('DROP TABLE sonata_abstract_user');
        $this->addSql('DROP TABLE member_user_group_action_entity');
    }
}
