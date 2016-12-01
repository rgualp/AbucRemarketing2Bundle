<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161025165946 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE newsletter ADD type INT NOT NULL, ADD schedule_date DATETIME DEFAULT NULL, ADD sent_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE newsletter_email ADD name VARCHAR(400) NOT NULL, ADD sms VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE newsletter DROP type, DROP schedule_date, DROP sent_date');
        $this->addSql('ALTER TABLE newsletter_email DROP name, DROP sms, CHANGE email email VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');

    }
}
