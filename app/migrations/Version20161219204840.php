<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161219204840 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP PROCEDURE IF EXISTS calculatePlacesYear");

        $this->addSql("
            CREATE PROCEDURE calculatePlacesYear(IN yearValue INT)
            BEGIN
                DECLARE done INT DEFAULT FALSE;
                DECLARE destinationId INT;
                DECLARE destinationsCursor CURSOR FOR select d.des_id from destination d;

                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

                set @year = yearValue;

                /*Lugares generales*/
              set @rownum = 0;
              UPDATE ownership_ranking_extra_year rankOuter
              JOIN (select @rownum := @rownum + 1 AS rownum,
              rank.accommodation, rank.ranking from ownership_ranking_extra_year rank
              WHERE rank.year = @year
              order by rank.ranking DESC, rank.totalAvailableRooms DESC, rank.totalNonAvailableRooms ASC, rank.totalReservedRooms DESC,
              rank.totalFacturation DESC) T on T.accommodation = rankOuter.accommodation
              set rankOuter.place = T.rownum
              WHERE rankOuter.year = @year;

              DROP TEMPORARY TABLE IF EXISTS placesDestination;
              CREATE TEMPORARY TABLE placesDestination (
                  place INT, idAccommodation INT
              );

                OPEN destinationsCursor;

                read_loop: LOOP
                  FETCH destinationsCursor INTO destinationId;
                  IF done THEN
                    LEAVE read_loop;
                  END IF;

                  set @rownumDestination = 0;
                  INSERT INTO placesDestination (place, idAccommodation)
                  select @rownumDestination := @rownumDestination + 1 AS rownum, P.accommodation
                  FROM
                  (SELECT rank.accommodation, rank.ranking
                  from ownership_ranking_extra_year rank
                  JOIN ownership o on o.own_id = rank.accommodation
                  WHERE o.own_destination = destinationId
                  and rank.year = @year
                  order by rank.place ASC) P;
                END LOOP;
                CLOSE destinationsCursor;

                /*Lugares por destino*/

                  UPDATE ownership_ranking_extra_year rank
                  JOIN placesDestination pDest on rank.accommodation = pDest.idAccommodation
                  set rank.destinationPlace = pDest.place
                  WHERE rank.year = @year;

                DROP TEMPORARY TABLE IF EXISTS placesDestination;
            END;
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP PROCEDURE IF EXISTS calculatePlacesYear");
    }
}
