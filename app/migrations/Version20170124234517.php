<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170124234517 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP FUNCTION IF EXISTS getTaxForServiceWithServiceFee');

        $this->addSql('CREATE FUNCTION getTaxForServiceWithServiceFee(rooms INT, nights INT, avgRoomsPrice DECIMAL(10,2), idServiceFee INT)
                        RETURNS DECIMAL(10,2)
                        BEGIN
                              DECLARE tax DECIMAL (10, 2);
                              IF nights = 1 THEN
                                  IF rooms = 1 THEN
                                      IF avgRoomsPrice < 20 THEN
                                          SET tax = (SELECT fee.one_nr_until_20_percent FROM servicefee fee WHERE fee.id = idServiceFee LIMIT 1);
                                      ELSEIF avgRoomsPrice >= 20 AND avgRoomsPrice < 25 THEN
                                          SET tax = (SELECT fee.one_nr_from_20_to_25_percent FROM servicefee fee WHERE fee.id = idServiceFee LIMIT 1);
                                      ELSE
                                          SET tax = (SELECT fee.one_nr_from_more_25_percent FROM servicefee fee WHERE fee.id = idServiceFee LIMIT 1);
                                      END IF;
                                  ELSE
                                      SET tax = (SELECT fee.one_night_several_rooms_percent FROM servicefee fee WHERE fee.id = idServiceFee LIMIT 1);
                                  END IF;
                              ELSEIF nights = 2 THEN
                                  SET tax = (SELECT fee.one_2_nights_percent FROM servicefee fee WHERE fee.id = idServiceFee LIMIT 1);
                              ELSEIF nights = 3 THEN
                                  SET tax = (SELECT fee.one_3_nights_percent FROM servicefee fee WHERE fee.id = idServiceFee LIMIT 1);
                              ELSEIF nights = 4 THEN
                                  SET tax = (SELECT fee.one_4_nights_percent FROM servicefee fee WHERE fee.id = idServiceFee LIMIT 1);
                              ELSE
                                  SET tax = (SELECT fee.one_5_nights_percent FROM servicefee fee WHERE fee.id = idServiceFee LIMIT 1);
                              END IF;
                             RETURN tax;
                        END');


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
