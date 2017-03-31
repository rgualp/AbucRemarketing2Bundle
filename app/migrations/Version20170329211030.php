<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170329211030 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            UPDATE ownership o
            SET o.own_name = o.own_old_name
            WHERE o.own_old_name is not null
            ');
        $this->addSql('
            DROP PROCEDURE IF EXISTS sp_rename_accommodations;');
        $this->addSql('
            CREATE PROCEDURE sp_rename_accommodations()
            BEGIN

			DECLARE done INT DEFAULT FALSE;
            DECLARE desName VARCHAR(500);
            DECLARE destinationsCursor CURSOR FOR select d.des_name from destination d;

            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

			DROP TEMPORARY TABLE IF EXISTS renamedAccommodations;
			CREATE TEMPORARY TABLE renamedAccommodations (idAccommodation INT, oldName VARCHAR(500), named VARCHAR(500));
            INSERT INTO renamedAccommodations (idAccommodation, oldName)
            SELECT own_id, own_name from ownership where own_name LIKE "%Casa Particular%";


            set @toReplace = "Casa Particular en La Habana ";
            set @toReplace1 = "Casa Particular en La Habana, ";
            set @toReplace2 = "Casa particular en La Habana, ";
            set @toReplace3 = "Casa particular en La Habana ";
            set @toReplace4 = "Casa Particular en la habana, ";
            set @toReplace5 = "Casa Particular en la habana ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(oldName, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");

            set @toReplace = "Casa Particular en Playa Larga ";
            set @toReplace1 = "Casa Particular en Playa Larga, ";
            set @toReplace2 = "Casa particular en Playa Larga, ";
            set @toReplace3 = "Casa particular en Playa Larga ";
            set @toReplace4 = "Casa Particular en playa larga, ";
            set @toReplace5 = "Casa Particular en playa larga ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");

            set @toReplace = "Casa Particular en Playa Girón ";
            set @toReplace1 = "Casa Particular en Playa Girón, ";
            set @toReplace2 = "Casa particular en Playa Girón, ";
            set @toReplace3 = "Casa particular en Playa Girón ";
            set @toReplace4 = "Casa Particular en playa girón, ";
            set @toReplace5 = "Casa Particular en playa girón ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");

             set @toReplace = "Casa Particular en Playa Giron ";
            set @toReplace1 = "Casa Particular en Playa Giron, ";
            set @toReplace2 = "Casa particular en Playa Giron, ";
            set @toReplace3 = "Casa particular en Playa Giron ";
            set @toReplace4 = "Casa Particular en playa giron, ";
            set @toReplace5 = "Casa Particular en playa giron ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");

            set @toReplace = "Casa Particular en La Isla de La Juventud ";
            set @toReplace1 = "Casa Particular en La Isla de La Juventud, ";
            set @toReplace2 = "Casa particular en La Isla de La Juventud, ";
            set @toReplace3 = "Casa particular en La Isla de La Juventud ";
            set @toReplace4 = "Casa Particular en la isla de la juventud, ";
            set @toReplace5 = "Casa Particular en la isla de la juventud ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");

            set @toReplace = "Casa Particular en la Isla de la Juventud ";
            set @toReplace1 = "Casa Particular en la Isla de la Juventud, ";
            set @toReplace2 = "Casa particular en la Isla de la Juventud, ";
            set @toReplace3 = "Casa particular en la Isla de la Juventud ";
            set @toReplace4 = "Casa Particular en la isla de la juventud, ";
            set @toReplace5 = "Casa Particular en la isla de la juventud ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");


            set @toReplace = "Casa Particular en Pinar del Rio ";
            set @toReplace1 = "Casa Particular en Pinar del Rio, ";
            set @toReplace2 = "Casa particular en Pinar del Rio, ";
            set @toReplace3 = "Casa particular en Pinar del Rio ";
            set @toReplace4 = "Casa Particular en pinar del rio, ";
            set @toReplace5 = "Casa Particular en pinar del rio ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");

            set @toReplace = "Casa Particular en Camaguey ";
            set @toReplace1 = "Casa Particular en Camaguey, ";
            set @toReplace2 = "Casa particular en Camaguey, ";
            set @toReplace3 = "Casa particular en Camaguey ";
            set @toReplace4 = "Casa Particular en camaguey, ";
            set @toReplace5 = "Casa Particular en camaguey ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");

            set @toReplace = "Casa Particular en Alamar ";
            set @toReplace1 = "Casa Particular en Alamar, ";
            set @toReplace2 = "Casa particular en Alamar, ";
            set @toReplace3 = "Casa particular en Alamar ";
            set @toReplace4 = "Casa Particular en alamar, ";
            set @toReplace5 = "Casa Particular en alamar ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");

            set @toReplace = "Casa Particular en Banes ";
            set @toReplace1 = "Casa Particular en Banes, ";
            set @toReplace2 = "Casa particular en Banes, ";
            set @toReplace3 = "Casa particular en Banes ";
            set @toReplace4 = "Casa Particular en banes, ";
            set @toReplace5 = "Casa Particular en banes ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");

            set @toReplace = "Casa Particular en Sancti Spiritus ";
            set @toReplace1 = "Casa Particular en Sancti Spiritus, ";
            set @toReplace2 = "Casa particular en Sancti Spiritus, ";
            set @toReplace3 = "Casa particular en Sancti Spiritus ";
            set @toReplace4 = "Casa Particular en sancti spiritus, ";
            set @toReplace5 = "Casa Particular en sancti spiritus ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");

            set @toReplace = "Casa Particular en Holguin ";
            set @toReplace1 = "Casa Particular en Holguin, ";
            set @toReplace2 = "Casa particular en Holguin, ";
            set @toReplace3 = "Casa particular en Holguin ";
            set @toReplace4 = "Casa Particular en holguin, ";
            set @toReplace5 = "Casa Particular en holguin ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");

            set @toReplace = "Casa Particular en Trinnidad ";
            set @toReplace1 = "Casa Particular en Trinnidad, ";
            set @toReplace2 = "Casa particular en Trinnidad, ";
            set @toReplace3 = "Casa particular en Trinnidad ";
            set @toReplace4 = "Casa Particular en trinnidad, ";
            set @toReplace5 = "Casa Particular en trinnidad ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");

            set @toReplace = "Casa Particular en Granma ";
            set @toReplace1 = "Casa Particular en Granma, ";
            set @toReplace2 = "Casa particular en Granma, ";
            set @toReplace3 = "Casa particular en Granma ";
            set @toReplace4 = "Casa Particular en granma, ";
            set @toReplace5 = "Casa Particular en granma ";

            UPDATE renamedAccommodations
            SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , "");


            OPEN destinationsCursor;

              read_loop: LOOP
                FETCH destinationsCursor INTO desName;
                IF done THEN
                  LEAVE read_loop;
                END IF;

                set @toReplace = CONCAT("Casa Particular en ", desName," ");
            	set @toReplace1 = CONCAT("Casa Particular en ", desName,", ");
            	set @toReplace2 = CONCAT("Casa particular en ", desName,", ");
            	set @toReplace3 = CONCAT("Casa particular en ", desName," ");
            	set @toReplace4 = CONCAT("Casa Particular en ", LOWER(desName),", ");
            	set @toReplace5 = CONCAT("Casa Particular en ", LOWER(desName)," ");
                set @toReplace6 = CONCAT(" Casa particular en ", desName);

                UPDATE renamedAccommodations
            	SET named = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(named, @toReplace , ""), @toReplace1 , ""), @toReplace2 , ""), @toReplace3 , ""), @toReplace4 , ""), @toReplace5 , ""), @toReplace6 , "");


              END LOOP;
              CLOSE destinationsCursor;

            set @toReplace = "CASA PARTICULAR EN LA HABANA ";
            UPDATE renamedAccommodations
            SET named = REPLACE(named, @toReplace , "");

            set @toReplace = "Casa Particular en ";
            UPDATE renamedAccommodations
            SET named = REPLACE(named, @toReplace , "");

            set @toReplace = "Casa Particular La Habana ";
            UPDATE renamedAccommodations
            SET named = REPLACE(named, @toReplace , "");

            set @toReplace = "Casa Particular Holguín ";
            UPDATE renamedAccommodations
            SET named = REPLACE(named, @toReplace , "");

            UPDATE ownership o
            set o.own_old_name = o.own_name
            where o.own_name LIKE "%casa particular%";

            UPDATE ownership o
            JOIN renamedAccommodations r on r.idAccommodation = o.own_id
            SET o.own_name = r.named
            where o.own_name LIKE "%casa particular%";

            UPDATE ownership o
            set o.own_old_name = o.own_name
            where o.own_name LIKE "%.";

            UPDATE ownership o
            set o.own_name = REPLACE(o.own_name, ".", "")
            where o.own_name LIKE "%.";

        END');

        $this->addSql('CALL sp_rename_accommodations;');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
