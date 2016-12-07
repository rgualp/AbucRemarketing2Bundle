<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161205213255 extends AbstractMigration
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
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation);

                    IF OLD.own_res_status != 5  AND NEW.own_res_status = 5  THEN
                      UPDATE ownershipdata
                      SET reservedRooms = reservedRooms + 1
                      WHERE accommodation = @accommodation;

                      set @reservations = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   JOIN booking b on b.booking_id = owres.own_res_reservation_booking
                                   JOIN payment p on p.booking_id = b.booking_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND owres.own_res_status = 5 );

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
                                   AND owres.own_res_status = 5);

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
                        set @sd = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND owres.own_res_status IN (1, 2, 4, 5, 6) );
                        set @snd = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND owres.own_res_status = 3 );

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
                    IF OLD.own_res_status != 5  AND NEW.own_res_status = 5  THEN
                      UPDATE ownershipdata
                      SET reservedRooms = reservedRooms + 1
                      WHERE accommodation = (SELECT MIN(gres.gen_res_own_id) FROM generalreservation gres where gres.gen_res_id = NEW.own_res_gen_res_id);
                    ELSEIF OLD.own_res_status = 5  AND NEW.own_res_status != 5 THEN
                        UPDATE ownershipdata
                          SET reservedRooms = reservedRooms - 1
                          WHERE accommodation = (SELECT MIN(gres.gen_res_own_id) FROM generalreservation gres where gres.gen_res_id = NEW.own_res_gen_res_id);
                    END IF;
                END;
        ");

    }
}
