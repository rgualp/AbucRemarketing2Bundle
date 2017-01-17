<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170117193734 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS ownershipreservation_after_insert_trigger");
        //Tabla ownershipreservation
        $this->addSql("
                CREATE TRIGGER ownershipreservation_after_insert_trigger AFTER INSERT ON ownershipreservation
                  FOR EACH ROW
                BEGIN
                    set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NOW()),INTERVAL DAY(LAST_DAY(NOW()))-1 DAY);
                    set @lastOfCurrentMonth = LAST_DAY(NOW());
                    set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                    set @accommodation = (SELECT MIN(gres.gen_res_own_id) FROM generalreservation gres where gres.gen_res_id = NEW.own_res_gen_res_id);
                    set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(NOW()));
                    set @awards = IF(@awards >= 1, 5, 0);
                    set @penalties = (SELECT COUNT(*) FROM penalty WHERE creationDate <= CURDATE() AND finalizationDate >= CURDATE() AND accommodation = @accommodation);
                    set @penaltiesRanking = IF(@penalties > 0, 5, 0);

                    set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                    set @total = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation  AND owres.own_res_status IN (1, 2, 4, 5, 6)
                                   AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer)
                                 );


                    IF NEW.own_res_status = 1 OR NEW.own_res_status = 2 OR NEW.own_res_status = 3  THEN
                        set @total = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer));

                        set @sd = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND owres.own_res_status IN (1, 2, 4, 5, 6) AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer));
                        set @snd = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND owres.own_res_status = 3 AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer));

                        set @totalAvailableFacturation = (select COALESCE(SUM(IF((owres.own_res_night_price is null or owres.own_res_night_price = 0) , COALESCE(owres.own_res_total_in_site, 0), owres.own_res_night_price * DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date))), 0) as total
                              from generalreservation gres
                              join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
                              where gres.gen_res_own_id = @accommodation
                              and owres.own_res_status IN (1, 2, 4, 5, 6)
                              and gres.gen_res_date >= @firstOfCurrentMonth and gres.gen_res_date <= @lastOfCurrentMonth);

            		  set @totalNonAvailableFacturation = (select COALESCE(SUM(IF((owres.own_res_night_price is null or owres.own_res_night_price = 0) , COALESCE(owres.own_res_total_in_site, 0), owres.own_res_night_price * DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date))), 0) as total
                          from generalreservation gres
                          join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
                          where gres.gen_res_own_id = @accommodation
                          and owres.own_res_status = 3
                          and gres.gen_res_date >= @firstOfCurrentMonth and gres.gen_res_date <= @lastOfCurrentMonth);

                      set @percent = (SELECT o.own_commission_percent / 100 from ownership o where o.own_id = @accommodation LIMIT 1);
                      set @totalAvailableFacturation =  @totalAvailableFacturation*(1-@percent);
                      set @totalNonAvailableFacturation =  @totalNonAvailableFacturation*(1-@percent);

                        set @sdPercent = COALESCE((@sd / @total) * 100, 0);
                        set @sndPercent = COALESCE((@snd / @total) * 100, 0);

                        set @sdRanking = IF(@sdPercent = 100, 5, IF(@sdPercent >= 50, 3, IF(@sdPercent < 33, 0, 1)));
                        set @sndRanking = IF(@snd = 0, 0, IF(@sndPercent >= 33, 5, IF(@sndPercent < 33 and @snd > 1, 3, 1)));

                        IF @exists = 0 THEN
                            INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,sd,snd,rankingPoints, awards, penalties, totalAvailableRooms, totalNonAvailableRooms, totalAvailableFacturation, totalNonAvailableFacturation)
                            VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @sdRanking, @sndRanking, @rankingPoints, @awards, @penaltiesRanking, @sd, @snd, @totalAvailableFacturation, @totalNonAvailableFacturation);
                        ELSE
                            UPDATE ownership_ranking_extra rank
                            SET rank.sd = @sdRanking,
                                rank.snd = @sndRanking,
                                rank.penalties = @penaltiesRanking,
                                rank.awards = @awards,
                                rank.totalAvailableRooms = @sd,
                                rank.totalNonAvailableRooms = @snd,
                                rank.totalAvailableFacturation = @totalAvailableFacturation,
                                rank.totalNonAvailableFacturation = @totalNonAvailableFacturation
                            WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                        END IF;
                    END IF;
                END;
        ");

        $this->addSql("update ownership_ranking_extra rank
                       set rank.totalAvailableRooms = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                                           WHERE gres.gen_res_date >= rank.startDate AND gres.gen_res_date <= rank.endDate AND gres.gen_res_own_id = rank.accommodation
                                                           AND owres.own_res_status IN (1, 2, 4, 5, 6) AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer)),
                       rank.totalNonAvailableRooms = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                                           WHERE gres.gen_res_date >= rank.startDate AND gres.gen_res_date <= rank.endDate AND gres.gen_res_own_id = rank.accommodation
                                                           AND owres.own_res_status = 3 AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer)),
                       rank.totalAvailableFacturation = (select COALESCE(SUM(IF((owres.own_res_night_price is null or owres.own_res_night_price = 0) , COALESCE(owres.own_res_total_in_site, 0), owres.own_res_night_price * DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date))), 0) as total
                                                      from generalreservation gres
                                                      join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
                                                      where gres.gen_res_own_id = rank.accommodation
                                                      and owres.own_res_status IN (1, 2, 4, 5, 6)
                                                      and gres.gen_res_date >= rank.startDate and gres.gen_res_date <= rank.endDate),
                       rank.totalNonAvailableFacturation = (select COALESCE(SUM(IF((owres.own_res_night_price is null or owres.own_res_night_price = 0) , COALESCE(owres.own_res_total_in_site, 0), owres.own_res_night_price * DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date))), 0) as total
                                                  from generalreservation gres
                                                  join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
                                                  where gres.gen_res_own_id = rank.accommodation
                                                  and owres.own_res_status = 3
                                                  and gres.gen_res_date >= rank.startDate and gres.gen_res_date <= rank.endDate)

                       where (rank.totalAvailableRooms is null or rank.totalNonAvailableRooms is null or rank.totalAvailableFacturation is null or rank.totalNonAvailableFacturation is null) and rank.startDate = '2017-01-01';");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS ownershipreservation_after_insert_trigger");
    }
}
