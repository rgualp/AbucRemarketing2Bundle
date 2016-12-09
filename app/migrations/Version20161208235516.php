<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161208235516 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP PROCEDURE IF EXISTS calculatePlacesByDestination");
        //Triggers en ownership
        $this->addSql("
            CREATE PROCEDURE calculatePlacesByDestination(IN monthValue INT, IN yearValue INT)
            BEGIN
              DECLARE done INT DEFAULT FALSE;
              DECLARE destinationId INT;
              DECLARE destinationsCursor CURSOR FOR select d.des_id from destination d;

              DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

              set @date = DATE_ADD(MAKEDATE(yearValue, 1), INTERVAL (monthValue)-1 MONTH);

              set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(@date),INTERVAL DAY(LAST_DAY(@date))-1 DAY);
              set @lastOfCurrentMonth = LAST_DAY(@date);

              /*Lugares generales*/
            set @rownum = 0;
            UPDATE ownership_ranking_extra rankOuter
            JOIN (select @rownum := @rownum + 1 AS rownum,
            rank.accommodation, rank.ranking from ownership_ranking_extra rank
            WHERE rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth
            order by rank.ranking DESC, rank.fad DESC,
            rank.failureCasa ASC, rank.penalties ASC,
            rank.sd DESC, rank.snd ASC, rank.confidence DESC, rank.ri DESC, rank.rr DESC, rank.reservations DESC,
            rank.awards DESC, rank.negativeComments ASC, rank.newOffersReserved DESC, rank.positiveComments DESC,
            rank.failureClients DESC, rank.facturation DESC) T on T.accommodation = rankOuter.accommodation
            set rankOuter.place = T.rownum
            WHERE rankOuter.startDate = @firstOfCurrentMonth AND rankOuter.endDate = @lastOfCurrentMonth;

              OPEN destinationsCursor;

              read_loop: LOOP
                FETCH destinationsCursor INTO destinationId;
                IF done THEN
                  LEAVE read_loop;
                END IF;

                set @date = DATE_ADD(MAKEDATE(yearValue, 1), INTERVAL (monthValue)-1 MONTH);

                set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(@date),INTERVAL DAY(LAST_DAY(@date))-1 DAY);
                set @lastOfCurrentMonth = LAST_DAY(@date);
                set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);


                /*Lugares por destino*/
                set @rownum = 0;
                UPDATE ownership_ranking_extra rankOuter
                JOIN (select @rownum := @rownum + 1 AS rownum,
                rank.accommodation, rank.ranking
                from ownership_ranking_extra rank
                JOIN ownership o on o.own_id = rank.accommodation
                WHERE o.own_destination = destinationId
                and rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth
                order by rank.ranking DESC, rank.fad DESC,
                rank.failureCasa ASC, rank.penalties ASC,
                rank.sd DESC, rank.snd ASC, rank.confidence DESC, rank.ri DESC, rank.rr DESC, rank.reservations DESC,
                rank.awards DESC, rank.negativeComments ASC, rank.newOffersReserved DESC, rank.positiveComments DESC,
                rank.failureClients DESC, rank.facturation DESC) T on T.accommodation = rankOuter.accommodation
                set rankOuter.destinationPlace = T.rownum
                WHERE rankOuter.startDate = @firstOfCurrentMonth AND rankOuter.endDate = @lastOfCurrentMonth;

              END LOOP;
              CLOSE destinationsCursor;
END;
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP PROCEDURE IF EXISTS calculatePlacesByDestination");

    }
}
