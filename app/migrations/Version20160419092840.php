<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160419092840 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE userstaffmanager (user_staff_manager_id INT AUTO_INCREMENT NOT NULL, user_staff_manager_user INT DEFAULT NULL, INDEX IDX_F1CC0BEAA24A90A3 (user_staff_manager_user), PRIMARY KEY(user_staff_manager_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE userstaffmanager_destination (user_staff_manager INT NOT NULL, destination INT NOT NULL, INDEX IDX_2F6D96F481C9F5F (user_staff_manager), INDEX IDX_2F6D96F3EC63EAA (destination), PRIMARY KEY(user_staff_manager, destination)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE userstaffmanager ADD CONSTRAINT FK_F1CC0BEAA24A90A3 FOREIGN KEY (user_staff_manager_user) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE userstaffmanager_destination ADD CONSTRAINT FK_2F6D96F481C9F5F FOREIGN KEY (user_staff_manager) REFERENCES userstaffmanager (user_staff_manager_id)');
        $this->addSql('ALTER TABLE userstaffmanager_destination ADD CONSTRAINT FK_2F6D96F3EC63EAA FOREIGN KEY (destination) REFERENCES destination (des_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE userstaffmanager_destination DROP FOREIGN KEY FK_2F6D96F481C9F5F');
        $this->addSql('DROP TABLE userstaffmanager');
        $this->addSql('DROP TABLE userstaffmanager_destination');
    }
}
