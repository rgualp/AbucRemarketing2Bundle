<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170113151402 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE cancel_type (cancel_id INT AUTO_INCREMENT NOT NULL, cancel_name VARCHAR(255) NOT NULL, PRIMARY KEY(cancel_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE cancel_payment (cancel_id INT AUTO_INCREMENT NOT NULL, reason VARCHAR(500) DEFAULT NULL, cancel_date DATETIME NOT NULL,type INT NOT NULL,give_tourist TINYINT(1) NOT NULL,INDEX IDX_B6BD307FEBA5B1E8 (type),PRIMARY KEY(cancel_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE cancel_payment ADD CONSTRAINT FK_B6BD307FEBA5B1E8 FOREIGN KEY (type) REFERENCES cancel_type (cancel_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE cancel_type');
    }
}
