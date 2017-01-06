<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161221231330 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("DROP TRIGGER IF EXISTS generalreservation_after_update_trigger;");
        //Tabla ownershipreservation
        $this->addSql("
                CREATE TRIGGER generalreservation_after_update_trigger AFTER UPDATE ON generalreservation
                  FOR EACH ROW
                BEGIN
                    set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NOW()),INTERVAL DAY(LAST_DAY(NOW()))-1 DAY);
                    set @lastOfCurrentMonth = LAST_DAY(NOW());
                    set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                    set @accommodation = NEW.gen_res_own_id;
                    set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(NOW()));
                    set @awards = IF(@awards >= 1, 5, 0);
                    set @penalties = (SELECT COUNT(*) FROM penalty WHERE creationDate <= CURDATE() AND finalizationDate >= CURDATE() AND accommodation = @accommodation);
                    set @penaltiesRanking = IF(@penalties > 0, 5, 0);

                    set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                    set @totalNewOffers = (SELECT COUNT(*) FROM generalreservation gres JOIN offerlog offer on gres.gen_res_id = offer.log_offer_reservation
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation);

                    set @payedNewOffers = (SELECT COUNT(DISTINCT gres.gen_res_id) FROM generalreservation gres
                                   JOIN ownershipreservation owres on gres.gen_res_id = owres.own_res_gen_res_id
                                   JOIN offerlog offer on gres.gen_res_id = offer.log_offer_reservation
                                   JOIN payment p on p.booking_id = owres.own_res_reservation_booking
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND gres.gen_res_status = 2 );

                    set @payedNewOffersPercent = COALESCE((@payedNewOffers / @totalNewOffers) * 100, 0);
                    set @payedNewOffersRanking = IF(@payedNewOffersPercent = 0, 0, IF(@payedNewOffersPercent = 100, 5, IF(@payedNewOffersPercent <= 33, 3, 1)));

                    IF @exists = 0 THEN
                         INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,newOffersReserved,rankingPoints, awards, penalties)
                         VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @payedNewOffersRanking, @rankingPoints, @awards, @penaltiesRanking);
                    ELSE
                         UPDATE ownership_ranking_extra rank
                         SET rank.newOffersReserved = @payedNewOffersRanking,
                             rank.penalties = @penaltiesRanking,
                             rank.awards = @awards
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

        $this->addSql("DROP TRIGGER IF EXISTS generalreservation_after_update_trigger;");
        //Tabla ownershipreservation
        $this->addSql("
                CREATE TRIGGER generalreservation_after_update_trigger AFTER UPDATE ON generalreservation
                  FOR EACH ROW
                BEGIN
                    set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NOW()),INTERVAL DAY(LAST_DAY(NOW()))-1 DAY);
                    set @lastOfCurrentMonth = LAST_DAY(NOW());
                    set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                    set @accommodation = NEW.gen_res_own_id;
                    set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(NOW()));
                    set @awards = IF(@awards >= 1, 5, 0);
                    set @penalties = (SELECT COUNT(*) FROM penalty WHERE creationDate <= CURDATE() AND finalizationDate >= CURDATE() AND accommodation = @accommodation);
                    set @penaltiesRanking = IF(@penalties > 0, 5, 0);

                    set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                    set @totalNewOffers = (SELECT COUNT(*) FROM generalreservation gres JOIN offerlog offer on gres.gen_res_id = offer.log_offer_reservation
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation);

                    set @payedNewOffers = (SELECT COUNT(DISTINCT gres.gen_res_id) FROM generalreservation gres
                                   JOIN ownershipreservation owres on gres.gen_res_id = owres.own_res_gen_res_id
                                   JOIN offerlog offer on gres.gen_res_id = offer.log_offer_reservation
                                   JOIN payment p on p.booking_id = owres.own_res_reservation_booking
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND gres.gen_res_status = 2 );

                    set @payedNewOffersPercent = COALESCE((@payedNewOffers / @totalNewOffers) * 100, 0);
                    set @payedNewOffersRanking = IF(@payedNewOffersPercent = 0, 0, IF(@payedNewOffersPercent = 100, 5, IF(@payedNewOffersPercent <= 33, 3, 1)));

                    IF @exists = 0 THEN
                         INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,newOffersReserved,rankingPoints, awards, penalties)
                         VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @payedNewOffersRanking, @rankingPoints, @awards, @penaltiesRanking);
                    ELSE
                         UPDATE ownership_ranking_extra rank
                         SET rank.newOffersReserved = @payedNewOffersRanking,
                             rank.penalties = @penaltiesRanking,
                             rank.awards = @awards
                         WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                    END IF;
                END;
        ");

    }
}
