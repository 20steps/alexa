<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170729211651 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bricks_custom_twentysteps_alexa_access_token (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_B8CB17EE5F37A13B (token), INDEX IDX_B8CB17EE19EB6921 (client_id), INDEX IDX_B8CB17EEA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bricks_custom_twentysteps_alexa_auth_code (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, redirect_uri LONGTEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_E97919F55F37A13B (token), INDEX IDX_E97919F519EB6921 (client_id), INDEX IDX_E97919F5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bricks_custom_twentysteps_alexa_client (id INT AUTO_INCREMENT NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', secret VARCHAR(255) NOT NULL, allowed_grant_types LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bricks_custom_twentysteps_alexa_login (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_6E1BB0BFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bricks_custom_twentysteps_alexa_refresh_token (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_C39A6E4A5F37A13B (token), INDEX IDX_C39A6E4A19EB6921 (client_id), INDEX IDX_C39A6E4AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bricks_custom_twentysteps_alexa_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', registration_type VARCHAR(255) NOT NULL, facebook_id VARCHAR(255) DEFAULT NULL, facebook_access_token VARCHAR(255) DEFAULT NULL, facebook_avatar_url VARCHAR(512) DEFAULT NULL, twitter_id VARCHAR(255) DEFAULT NULL, twitter_access_token VARCHAR(255) DEFAULT NULL, twitter_avatar_url VARCHAR(512) DEFAULT NULL, email_confirmation_token VARCHAR(255) DEFAULT NULL, email_request VARCHAR(255) DEFAULT NULL, email_requested_at DATETIME DEFAULT NULL, slug VARCHAR(128) NOT NULL, saluation VARCHAR(255) DEFAULT NULL, first_name LONGTEXT DEFAULT NULL, last_name LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, avatar VARCHAR(255) DEFAULT NULL, settings LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', avatar_filename VARCHAR(255) DEFAULT NULL, reset_password_token_requested_at DATETIME DEFAULT NULL, reset_password_token VARCHAR(255) DEFAULT NULL, activation_token_requested_at DATETIME DEFAULT NULL, activation_token VARCHAR(255) DEFAULT NULL, new_email VARCHAR(255) DEFAULT NULL, google_id VARCHAR(255) DEFAULT NULL, google_access_token VARCHAR(255) DEFAULT NULL, google_avatar_url VARCHAR(512) DEFAULT NULL, digits_id VARCHAR(255) DEFAULT NULL, digits_phone_number VARCHAR(255) DEFAULT NULL, digits_email VARCHAR(255) DEFAULT NULL, digits_access_token VARCHAR(255) DEFAULT NULL, digits_secret VARCHAR(255) DEFAULT NULL, digits_email_is_verified TINYINT(1) DEFAULT NULL, digits_verification_type VARCHAR(255) DEFAULT NULL, digits_avatar_url VARCHAR(512) DEFAULT NULL, linked_in_id VARCHAR(255) DEFAULT NULL, linked_in_access_token VARCHAR(255) DEFAULT NULL, linked_in_avatar_url VARCHAR(512) DEFAULT NULL, statistic_interaction_count INT DEFAULT 0, UNIQUE INDEX UNIQ_FFA2C1B092FC23A8 (username_canonical), UNIQUE INDEX UNIQ_FFA2C1B0A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_FFA2C1B0C05FB297 (confirmation_token), UNIQUE INDEX UNIQ_FFA2C1B0989D9B62 (slug), UNIQUE INDEX unique_email_idx (email), UNIQUE INDEX unique_username_idx (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_access_token ADD CONSTRAINT FK_B8CB17EE19EB6921 FOREIGN KEY (client_id) REFERENCES bricks_custom_twentysteps_alexa_client (id)');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_access_token ADD CONSTRAINT FK_B8CB17EEA76ED395 FOREIGN KEY (user_id) REFERENCES bricks_custom_twentysteps_alexa_user (id)');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_auth_code ADD CONSTRAINT FK_E97919F519EB6921 FOREIGN KEY (client_id) REFERENCES bricks_custom_twentysteps_alexa_client (id)');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_auth_code ADD CONSTRAINT FK_E97919F5A76ED395 FOREIGN KEY (user_id) REFERENCES bricks_custom_twentysteps_alexa_user (id)');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_login ADD CONSTRAINT FK_6E1BB0BFA76ED395 FOREIGN KEY (user_id) REFERENCES bricks_custom_twentysteps_alexa_user (id)');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_refresh_token ADD CONSTRAINT FK_C39A6E4A19EB6921 FOREIGN KEY (client_id) REFERENCES bricks_custom_twentysteps_alexa_client (id)');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_refresh_token ADD CONSTRAINT FK_C39A6E4AA76ED395 FOREIGN KEY (user_id) REFERENCES bricks_custom_twentysteps_alexa_user (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_access_token DROP FOREIGN KEY FK_B8CB17EE19EB6921');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_auth_code DROP FOREIGN KEY FK_E97919F519EB6921');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_refresh_token DROP FOREIGN KEY FK_C39A6E4A19EB6921');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_access_token DROP FOREIGN KEY FK_B8CB17EEA76ED395');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_auth_code DROP FOREIGN KEY FK_E97919F5A76ED395');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_login DROP FOREIGN KEY FK_6E1BB0BFA76ED395');
        $this->addSql('ALTER TABLE bricks_custom_twentysteps_alexa_refresh_token DROP FOREIGN KEY FK_C39A6E4AA76ED395');
        $this->addSql('DROP TABLE bricks_custom_twentysteps_alexa_access_token');
        $this->addSql('DROP TABLE bricks_custom_twentysteps_alexa_auth_code');
        $this->addSql('DROP TABLE bricks_custom_twentysteps_alexa_client');
        $this->addSql('DROP TABLE bricks_custom_twentysteps_alexa_login');
        $this->addSql('DROP TABLE bricks_custom_twentysteps_alexa_refresh_token');
        $this->addSql('DROP TABLE bricks_custom_twentysteps_alexa_user');
    }
}
