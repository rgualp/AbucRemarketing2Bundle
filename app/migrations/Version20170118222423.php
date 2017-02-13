<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170118222423 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pa_pending_payment_accommodation (id INT AUTO_INCREMENT NOT NULL, booking INT NOT NULL, reservation INT NOT NULL, agency INT NOT NULL, type INT DEFAULT NULL, created_date DATETIME NOT NULL, pay_date DATETIME NOT NULL, amount DOUBLE PRECISION DEFAULT NULL, INDEX IDX_B48B41B5E00CEDDE (booking), INDEX IDX_B48B41B542C84955 (reservation), INDEX IDX_B48B41B570C0C6E6 (agency), INDEX IDX_B48B41B58CDE5729 (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pa_pending_payment_accommodation');

    }
}
