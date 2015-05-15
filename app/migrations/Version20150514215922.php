<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150514215922 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ownership CHANGE own_name own_name VARCHAR(255) DEFAULT NULL, CHANGE own_licence_number own_licence_number VARCHAR(255) DEFAULT NULL, CHANGE own_address_street own_address_street VARCHAR(255) DEFAULT NULL, CHANGE own_address_number own_address_number VARCHAR(255) DEFAULT NULL, CHANGE own_address_between_street_1 own_address_between_street_1 VARCHAR(255) DEFAULT NULL, CHANGE own_address_between_street_2 own_address_between_street_2 VARCHAR(255) DEFAULT NULL, CHANGE own_mobile_number own_mobile_number VARCHAR(255) DEFAULT NULL, CHANGE own_homeowner_1 own_homeowner_1 VARCHAR(255) DEFAULT NULL, CHANGE own_homeowner_2 own_homeowner_2 VARCHAR(255) DEFAULT NULL, CHANGE own_phone_number own_phone_number VARCHAR(255) DEFAULT NULL, CHANGE own_email_2 own_email_2 VARCHAR(255) DEFAULT NULL, CHANGE own_category own_category VARCHAR(255) DEFAULT NULL, CHANGE own_type own_type VARCHAR(255) DEFAULT NULL, CHANGE own_facilities_breakfast_price own_facilities_breakfast_price VARCHAR(255) DEFAULT NULL, CHANGE own_facilities_dinner_price_from own_facilities_dinner_price_from VARCHAR(255) DEFAULT NULL, CHANGE own_facilities_dinner_price_to own_facilities_dinner_price_to VARCHAR(255) DEFAULT NULL, CHANGE own_facilities_parking_price own_facilities_parking_price VARCHAR(255) DEFAULT NULL, CHANGE own_geolocate_y own_geolocate_y VARCHAR(255) DEFAULT NULL, CHANGE own_rating own_rating NUMERIC(10, 0) DEFAULT NULL, CHANGE own_maximun_number_guests own_maximun_number_guests INT DEFAULT NULL, CHANGE own_minimum_price own_minimum_price INT DEFAULT NULL, CHANGE own_maximum_price own_maximum_price INT DEFAULT NULL, CHANGE own_comments_total own_comments_total INT DEFAULT NULL, CHANGE own_langs own_langs VARCHAR(4) DEFAULT NULL, CHANGE own_geolocate_x own_geolocate_x VARCHAR(255) DEFAULT NULL, CHANGE own_sync_st own_sync_st INT DEFAULT NULL, CHANGE own_commission_percent own_commission_percent INT DEFAULT NULL, CHANGE own_rooms_total own_rooms_total INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ownership CHANGE own_name own_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_licence_number own_licence_number VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_address_street own_address_street VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_address_number own_address_number VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_address_between_street_1 own_address_between_street_1 VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_address_between_street_2 own_address_between_street_2 VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_mobile_number own_mobile_number VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_homeowner_1 own_homeowner_1 VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_homeowner_2 own_homeowner_2 VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_phone_number own_phone_number VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_email_2 own_email_2 VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_category own_category VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_type own_type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_facilities_breakfast_price own_facilities_breakfast_price VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_facilities_dinner_price_from own_facilities_dinner_price_from VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_facilities_dinner_price_to own_facilities_dinner_price_to VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_facilities_parking_price own_facilities_parking_price VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_langs own_langs VARCHAR(4) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_geolocate_x own_geolocate_x VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_geolocate_y own_geolocate_y VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE own_commission_percent own_commission_percent INT NOT NULL, CHANGE own_rating own_rating NUMERIC(10, 0) NOT NULL, CHANGE own_maximun_number_guests own_maximun_number_guests INT NOT NULL, CHANGE own_minimum_price own_minimum_price INT NOT NULL, CHANGE own_maximum_price own_maximum_price INT NOT NULL, CHANGE own_comments_total own_comments_total INT NOT NULL, CHANGE own_rooms_total own_rooms_total INT NOT NULL, CHANGE own_sync_st own_sync_st INT NOT NULL');
    }
}
