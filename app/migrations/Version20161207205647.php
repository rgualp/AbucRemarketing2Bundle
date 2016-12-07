<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161207205647 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS payment_after_insert_trigger");

        $this->addSql("
                CREATE TRIGGER payment_after_insert_trigger AFTER INSERT ON payment
                  FOR EACH ROW
                BEGIN
                      DECLARE done INT DEFAULT FALSE;
                      DECLARE accommodation INT;
                      DECLARE accommodationsCursor CURSOR FOR select DISTINCT gres.gen_res_own_id from generalreservation gres
                                              join ownershipreservation owres on gres.gen_res_id = owres.own_res_gen_res_id
                                              where owres.`own_res_reservation_booking` = NEW.booking_id;

                      DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;


                      set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NEW.created),INTERVAL DAY(LAST_DAY(NEW.created))-1 DAY);
                      set @lastOfCurrentMonth = LAST_DAY(NEW.created);
                      set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);

                      OPEN accommodationsCursor;

                      read_loop: LOOP
                        FETCH accommodationsCursor INTO accommodation;
                        IF done THEN
                          LEAVE read_loop;
                        END IF;

                        set @accommodation = accommodation;
                        set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(NEW.created));
                        set @awards = IF(@awards >= 1, 5, 0);

                        set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                        set @penalties = (SELECT COUNT(*) FROM penalty p
                                          WHERE p.creationDate <= NEW.created AND p.finalizationDate >= NEW.created
                                          AND p.accommodation = @accommodation);
                        set @penaltiesRanking = IF(@penalties > 0, 5, 0);

                        set @total = (select COALESCE(SUM(IF((owres.own_res_night_price is null or owres.own_res_night_price = 0) , COALESCE(owres.own_res_total_in_site, 0), owres.own_res_night_price * DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date))), 0) as total
                                      from generalreservation gres
                                      join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
                                      join payment p on p.booking_id = owres.own_res_reservation_booking
                                      where gres.gen_res_id NOT IN (select f.reservation from failure f where f.accommodation = @accommodation )
                                      and gres.gen_res_own_id = @accommodation
                                      and gres.gen_res_status = 2
                                      and gres.gen_res_date >= @firstOfCurrentMonth and gres.gen_res_date <= @lastOfCurrentMonth
                                      and p.status in (1, 4));

                        set @percent = (SELECT o.own_commission_percent / 100 from ownership o where o.own_id = @accommodation LIMIT 1);

                        set @total =  @total*(1-@percent);

                        set @faRanking = IF(@total = 0, 0, IF(@total < 250, 1, IF(@total >= 450, 5, 2)));

                        IF @exists = 0 THEN
                           INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,rankingPoints, awards, penalties, facturation)
                           VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @rankingPoints, @awards, @penaltiesRanking, @faRanking);
                        ELSE
                           UPDATE ownership_ranking_extra rank
                           SET rank.penalties = @penaltiesRanking,
                               rank.awards = @awards,
                               rank.facturation = @faRanking
                           WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                        END IF;
                      END LOOP;

                      CLOSE accommodationsCursor;
                END;
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS payment_after_insert_trigger");

    }
}
