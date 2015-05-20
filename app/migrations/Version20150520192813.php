<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150520192813 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE clientComment (comment_id INT AUTO_INCREMENT NOT NULL, comment_client_user INT DEFAULT NULL, comment_staff_user INT DEFAULT NULL, comment_text LONGTEXT NOT NULL, comment_date DATETIME NOT NULL, INDEX IDX_F75CC7C067E3D469 (comment_client_user), INDEX IDX_F75CC7C0ED442AE6 (comment_staff_user), PRIMARY KEY(comment_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE clientComment ADD CONSTRAINT FK_F75CC7C067E3D469 FOREIGN KEY (comment_client_user) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE clientComment ADD CONSTRAINT FK_F75CC7C0ED442AE6 FOREIGN KEY (comment_staff_user) REFERENCES user (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE clientComment');
    }
}
