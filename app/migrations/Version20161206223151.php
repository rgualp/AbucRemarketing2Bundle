<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161206223151 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS failure_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS failure_after_delete_trigger");

        $this->addSql("
                CREATE TRIGGER failure_after_insert_trigger AFTER INSERT ON failure
                  FOR EACH ROW
                BEGIN
                    set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NEW.creationDate),INTERVAL DAY(LAST_DAY(NEW.creationDate))-1 DAY);
                    set @lastOfCurrentMonth = LAST_DAY(NEW.creationDate);
                    set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                    set @accommodation = NEW.accommodation;
                    set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(NEW.creationDate));
                    set @awards = IF(@awards >= 1, 5, 0);

                    set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                    set @accommodationFailureType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'failureType' AND nom.nom_name = 'accommodation_failure');
                    set @touristFailureType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'failureType' AND nom.nom_name = 'tourist_failure');

                    set @accommodationFailures = (SELECT COUNT(fail.id) FROM failure fail
                                   WHERE fail.creationDate >= @firstOfCurrentMonth AND fail.creationDate <= @lastOfCurrentMonth
                                   AND fail.accommodation = @accommodation AND fail.type = @accommodationFailureType);

                    set @touristFailures = (SELECT COUNT(fail.id) FROM failure fail
                                   WHERE fail.creationDate >= @firstOfCurrentMonth AND fail.creationDate <= @lastOfCurrentMonth
                                   AND fail.accommodation = @accommodation AND fail.type = @touristFailureType);

                    set @accommodationFailuresRanking = IF(@accommodationFailures = 0, 0, IF(@accommodationFailures = 1, 1, IF(@accommodationFailures >= 3, 5, 3)));

                    set @touristFailuresRanking = IF(@touristFailures = 0, 0, IF(@touristFailures = 1, 1, IF(@touristFailures >= 3, 5, 3)));

                    IF @exists = 0 THEN
                         INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,failureCasa,rankingPoints, awards, failureClients)
                         VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @accommodationFailuresRanking, @rankingPoints, @awards, @touristFailuresRanking);
                    ELSE
                         UPDATE ownership_ranking_extra rank
                         SET rank.failureCasa = @accommodationFailuresRanking,
                             rank.failureClients = @touristFailuresRanking,
                             rank.awards = @awards
                         WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                    END IF;
                END;
        ");

        $this->addSql("
                CREATE TRIGGER failure_after_delete_trigger AFTER DELETE ON failure
                  FOR EACH ROW
                BEGIN
                    set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(OLD.creationDate),INTERVAL DAY(LAST_DAY(OLD.creationDate))-1 DAY);
                    set @lastOfCurrentMonth = LAST_DAY(OLD.creationDate);
                    set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                    set @accommodation = OLD.accommodation;
                    set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(OLD.creationDate));
                    set @awards = IF(@awards >= 1, 5, 0);

                    set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                    set @accommodationFailureType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'failureType' AND nom.nom_name = 'accommodation_failure');
                    set @touristFailureType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'failureType' AND nom.nom_name = 'tourist_failure');

                    set @accommodationFailures = (SELECT COUNT(fail.id) FROM failure fail
                                   WHERE fail.creationDate >= @firstOfCurrentMonth AND fail.creationDate <= @lastOfCurrentMonth
                                   AND fail.accommodation = @accommodation AND fail.type = @accommodationFailureType);

                    set @touristFailures = (SELECT COUNT(fail.id) FROM failure fail
                                   WHERE fail.creationDate >= @firstOfCurrentMonth AND fail.creationDate <= @lastOfCurrentMonth
                                   AND fail.accommodation = @accommodation AND fail.type = @touristFailureType);

                    set @accommodationFailuresRanking = IF(@accommodationFailures = 0, 0, IF(@accommodationFailures = 1, 1, IF(@accommodationFailures >= 3, 5, 3)));

                    set @touristFailuresRanking = IF(@touristFailures = 0, 0, IF(@touristFailures = 1, 1, IF(@touristFailures >= 3, 5, 3)));

                    IF @exists = 0 THEN
                         INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,failureCasa,rankingPoints, awards, failureClients)
                         VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @accommodationFailuresRanking, @rankingPoints, @awards, @touristFailuresRanking);
                    ELSE
                         UPDATE ownership_ranking_extra rank
                         SET rank.failureCasa = @accommodationFailuresRanking,
                             rank.failureClients = @touristFailuresRanking,
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
        $this->addSql("DROP TRIGGER IF EXISTS failure_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS failure_after_delete_trigger");

    }
}
