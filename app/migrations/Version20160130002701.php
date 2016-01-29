<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160130002701 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE old_payment (id INT AUTO_INCREMENT NOT NULL, creation_date DATE DEFAULT NULL, reservation_code VARCHAR(255) DEFAULT NULL, accommodation_code VARCHAR(255) DEFAULT NULL, tourist_full_name VARCHAR(255) DEFAULT NULL, tourist_email VARCHAR(255) DEFAULT NULL, tourist_country VARCHAR(255) DEFAULT NULL, currency_code VARCHAR(255) DEFAULT NULL, adults INT DEFAULT NULL, children INT DEFAULT NULL, arrival_date DATE DEFAULT NULL, nights INT DEFAULT NULL, rooms INT DEFAULT NULL, pay_at_accommodation DOUBLE PRECISION DEFAULT NULL, prepay_amount DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE old_reservation (id INT AUTO_INCREMENT NOT NULL, creation_date DATE DEFAULT NULL, accommodation_code VARCHAR(255) DEFAULT NULL, tourist_name VARCHAR(255) DEFAULT NULL, tourist_lastname VARCHAR(255) DEFAULT NULL, tourist_email VARCHAR(255) DEFAULT NULL, tourist_address VARCHAR(255) DEFAULT NULL, tourist_postal_code VARCHAR(255) DEFAULT NULL, tourist_city VARCHAR(255) DEFAULT NULL, tourist_country VARCHAR(255) DEFAULT NULL, tourist_phone VARCHAR(255) DEFAULT NULL, tourist_language VARCHAR(255) DEFAULT NULL, tourist_currency VARCHAR(255) DEFAULT NULL, adults INT DEFAULT NULL, children INT DEFAULT NULL, arrival_date DATE DEFAULT NULL, nights INT DEFAULT NULL, rooms INT DEFAULT NULL, comments LONGTEXT DEFAULT NULL, accommodation_name VARCHAR(255) DEFAULT NULL, accommodation_owners VARCHAR(255) DEFAULT NULL, accommodation_address VARCHAR(255) DEFAULT NULL, accommodation_phone VARCHAR(255) DEFAULT NULL, accommodation_cellphone VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP TABLE old_payment');
        $this->addSql('DROP TABLE old_reservation');

    }
}
