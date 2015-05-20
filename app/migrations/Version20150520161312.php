<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150520161312 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE message (message_id INT AUTO_INCREMENT NOT NULL, message_send_to INT DEFAULT NULL, message_sender INT DEFAULT NULL, message_subject VARCHAR(400) NOT NULL, mesage_body LONGTEXT NOT NULL, message_date DATETIME NOT NULL, INDEX IDX_B6BD307FEBA5B1E3 (message_send_to), INDEX IDX_B6BD307F6827D7C0 (message_sender), PRIMARY KEY(message_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FEBA5B1E3 FOREIGN KEY (message_send_to) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F6827D7C0 FOREIGN KEY (message_sender) REFERENCES user (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE message');
    }
}
