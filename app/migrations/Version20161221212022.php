<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161221212022 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS ownershipreservation_after_update_trigger");
        //Tabla ownershipreservation
        $this->addSql("
                CREATE TRIGGER ownershipreservation_after_update_trigger AFTER UPDATE ON ownershipreservation
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

                    IF OLD.own_res_status != 5  AND NEW.own_res_status = 5  THEN
                      UPDATE ownershipdata
                      SET reservedRooms = reservedRooms + 1
                      WHERE accommodation = @accommodation;

                      set @reservations = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   JOIN booking b on b.booking_id = owres.own_res_reservation_booking
                                   JOIN payment p on p.booking_id = b.booking_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND owres.own_res_status = 5
                                   AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer)
                                   );

                      set @reservationsPercent = COALESCE((@reservations / @total) * 100, 0);

                      set @reservationsRanking = IF(@reservationsPercent = 0, 0, IF(@reservationsPercent = 100, 5, IF(@reservationsPercent <= 33, 3, 1)));

                      IF @exists = 0 THEN
                            INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,reservations,rankingPoints, awards, penalties, totalReservedRooms)
                            VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @reservationsRanking, @rankingPoints, @awards, @penaltiesRanking, @reservations);
                        ELSE
                            UPDATE ownership_ranking_extra rank
                            SET rank.reservations = @reservationsRanking,
                                rank.penalties = @penaltiesRanking,
                                rank.awards = @awards,
                                rank.totalReservedRooms = @reservations
                            WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                        END IF;

                    ELSEIF OLD.own_res_status = 5  AND NEW.own_res_status != 5 THEN
                        UPDATE ownershipdata
                          SET reservedRooms = reservedRooms - 1
                          WHERE accommodation = @accommodation;

                        set @reservations = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   JOIN booking b on b.booking_id = owres.own_res_reservation_booking
                                   JOIN payment p on p.booking_id = b.booking_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND owres.own_res_status = 5
                                   AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer)
                                   );

                        set @reservationsPercent = COALESCE((@reservations / @total) * 100, 0);

                        set @reservationsRanking = IF(@reservationsPercent = 0, 0, IF(@reservationsPercent = 100, 5, IF(@reservationsPercent <= 33, 3, 1)));

                        IF @exists = 0 THEN
                                INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,reservations,rankingPoints, awards, penalties, totalReservedRooms)
                                VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @reservationsRanking, @rankingPoints, @awards, @penaltiesRanking, @reservations);
                        ELSE
                                UPDATE ownership_ranking_extra rank
                                SET rank.reservations = @reservationsRanking,
                                    rank.penalties = @penaltiesRanking,
                                    rank.awards = @awards,
                                    rank.totalReservedRooms = @reservations
                                WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                        END IF;
                    END IF;

                    IF OLD.own_res_status != NEW.own_res_status AND (NEW.own_res_status = 1 OR NEW.own_res_status = 2 OR NEW.own_res_status = 3)  THEN
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
                                rank.totalNonAvailableFacturation = @totalAvailableFacturation
                            WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                        END IF;
                    END IF;
                END;
        ");

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

                        set @reservations = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   JOIN booking b on b.booking_id = owres.own_res_reservation_booking
                                   JOIN payment p on p.booking_id = b.booking_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND owres.own_res_status = 5
                                   AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer)
                                   );

                        set @total = (select COALESCE(SUM(IF((owres.own_res_night_price is null or owres.own_res_night_price = 0) , COALESCE(owres.own_res_total_in_site, 0), owres.own_res_night_price * DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date))), 0) as total
                                      from generalreservation gres
                                      join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
                                      join payment p on p.booking_id = owres.own_res_reservation_booking
                                      where gres.gen_res_id NOT IN (select f.reservation from failure f where f.accommodation = @accommodation )
                                      and gres.gen_res_own_id = @accommodation
                                      and gres.gen_res_status = 2
                                      and gres.gen_res_date >= @firstOfCurrentMonth and gres.gen_res_date <= @lastOfCurrentMonth
                                      and p.status in (1, 4));

                        set @currentMonthFacturation = (select COALESCE(SUM(IF((owres.own_res_night_price is null or owres.own_res_night_price = 0) , COALESCE(owres.own_res_total_in_site, 0), owres.own_res_night_price * DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date))), 0) as total
                          from generalreservation gres
                          join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
                          join payment p on p.booking_id = owres.own_res_reservation_booking
                          where gres.gen_res_id NOT IN (select f.reservation from failure f where f.accommodation = @accommodation )
                          and gres.gen_res_own_id = @accommodation
                          and gres.gen_res_status = 2
                          and p.created >= @firstOfCurrentMonth and p.created <= @lastOfCurrentMonth
                          and p.status in (1, 4));

                        set @percent = (SELECT o.own_commission_percent / 100 from ownership o where o.own_id = @accommodation LIMIT 1);
                        set @total =  @total*(1-@percent);
                        set @currentMonthFacturation =  @currentMonthFacturation*(1-@percent);

                        set @faRanking = IF(@total = 0, 0, IF(@total < 250, 1, IF(@total >= 450, 5, 2)));

                        IF @exists = 0 THEN
                           INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,rankingPoints, awards, penalties, facturation, totalFacturation, currentMonthFacturation, totalReservedRooms)
                           VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @rankingPoints, @awards, @penaltiesRanking, @faRanking, @total, @currentMonthFacturation, @reservations);
                        ELSE
                           UPDATE ownership_ranking_extra rank
                           SET rank.penalties = @penaltiesRanking,
                               rank.awards = @awards,
                               rank.facturation = @faRanking,
                               rank.totalFacturation = @total,
                               rank.currentMonthFacturation = @currentMonthFacturation,
                               rank.totalReservedRooms = @reservations
                           WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                        END IF;
                      END LOOP;

                      CLOSE accommodationsCursor;
                END;
        ");

        $this->addSql("DROP TRIGGER IF EXISTS ownershipdata_after_update_trigger");
        //Tabla ownershipreservation
        $this->addSql("
                CREATE TRIGGER ownershipdata_after_update_trigger AFTER UPDATE ON ownershipdata
                  FOR EACH ROW
                BEGIN
                    set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NOW()),INTERVAL DAY(LAST_DAY(NOW()))-1 DAY);
                    set @lastOfCurrentMonth = LAST_DAY(NOW());
                    set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                    set @accommodation = NEW.accommodation;
                    set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(NOW()));
                    set @awards = IF(@awards >= 1, 5, 0);
                    set @penalties = (SELECT COUNT(*) FROM penalty WHERE creationDate <= CURDATE() AND finalizationDate >= CURDATE() AND accommodation = @accommodation);
                    set @penaltiesRanking = IF(@penalties > 0, 5, 0);

                    set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                    set @visits = NEW.visitsLastWeek;

                    IF @exists = 0 THEN
                            INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,rankingPoints, awards, penalties, visits)
                            VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @rankingPoints, @awards, @penaltiesRanking, @visits);
                    ELSE
                        UPDATE ownership_ranking_extra rank
                        SET rank.penalties = @penaltiesRanking,
                            rank.awards = @awards,
                            rank.visits = @visits
                        WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                    END IF;
                END;
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS ownershipreservation_after_update_trigger");
        //Tabla ownershipreservation
        $this->addSql("
                CREATE TRIGGER ownershipreservation_after_update_trigger AFTER UPDATE ON ownershipreservation
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

                    IF OLD.own_res_status != 5  AND NEW.own_res_status = 5  THEN
                      UPDATE ownershipdata
                      SET reservedRooms = reservedRooms + 1
                      WHERE accommodation = @accommodation;

                      set @reservations = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   JOIN booking b on b.booking_id = owres.own_res_reservation_booking
                                   JOIN payment p on p.booking_id = b.booking_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND owres.own_res_status = 5
                                   AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer)
                                   );

                      set @reservationsPercent = COALESCE((@reservations / @total) * 100, 0);

                      set @reservationsRanking = IF(@reservationsPercent = 0, 0, IF(@reservationsPercent = 100, 5, IF(@reservationsPercent <= 33, 3, 1)));

                      IF @exists = 0 THEN
                            INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,reservations,rankingPoints, awards, penalties)
                            VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @reservationsRanking, @rankingPoints, @awards, @penaltiesRanking);
                        ELSE
                            UPDATE ownership_ranking_extra rank
                            SET rank.reservations = @reservationsRanking,
                                rank.penalties = @penaltiesRanking,
                                rank.awards = @awards
                            WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                        END IF;

                    ELSEIF OLD.own_res_status = 5  AND NEW.own_res_status != 5 THEN
                        UPDATE ownershipdata
                          SET reservedRooms = reservedRooms - 1
                          WHERE accommodation = @accommodation;

                        set @reservations = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   JOIN booking b on b.booking_id = owres.own_res_reservation_booking
                                   JOIN payment p on p.booking_id = b.booking_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND owres.own_res_status = 5
                                   AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer)
                                   );

                        set @reservationsPercent = COALESCE((@reservations / @total) * 100, 0);

                        set @reservationsRanking = IF(@reservationsPercent = 0, 0, IF(@reservationsPercent = 100, 5, IF(@reservationsPercent <= 33, 3, 1)));

                        IF @exists = 0 THEN
                                INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,reservations,rankingPoints, awards, penalties)
                                VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @reservationsRanking, @rankingPoints, @awards, @penaltiesRanking);
                        ELSE
                                UPDATE ownership_ranking_extra rank
                                SET rank.reservations = @reservationsRanking,
                                    rank.penalties = @penaltiesRanking,
                                    rank.awards = @awards
                                WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                        END IF;
                    END IF;

                    IF OLD.own_res_status != 1  AND OLD.own_res_status != 2 AND (NEW.own_res_status = 1 OR NEW.own_res_status = 2)  THEN
                        set @total = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer));

                        set @sd = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND owres.own_res_status IN (1, 2, 4, 5, 6) AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer));
                        set @snd = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND owres.own_res_status = 3 AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer));

                        set @sdPercent = COALESCE((@sd / @total) * 100, 0);
                        set @sndPercent = COALESCE((@snd / @total) * 100, 0);

                        set @sdRanking = IF(@sdPercent = 100, 5, IF(@sdPercent >= 50, 3, IF(@sdPercent < 33, 0, 1)));
                        set @sndRanking = IF(@snd = 0, 0, IF(@sndPercent >= 33, 5, IF(@sndPercent < 33 and @snd > 1, 3, 1)));

                        IF @exists = 0 THEN
                            INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,sd,snd,rankingPoints, awards, penalties)
                            VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @sdRanking, @sndRanking, @rankingPoints, @awards, @penaltiesRanking);
                        ELSE
                            UPDATE ownership_ranking_extra rank
                            SET rank.sd = @sdRanking,
                                rank.snd = @sndRanking,
                                rank.penalties = @penaltiesRanking,
                                rank.awards = @awards
                            WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                        END IF;
                    END IF;
                END;
        ");

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

        $this->addSql("DROP TRIGGER IF EXISTS ownershipdata_after_update_trigger");
    }
}
