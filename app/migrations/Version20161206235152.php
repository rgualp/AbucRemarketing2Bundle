<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161206235152 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS penalty_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS penalty_after_delete_trigger");

        $this->addSql("
                CREATE TRIGGER penalty_after_insert_trigger AFTER INSERT ON penalty
                  FOR EACH ROW
                BEGIN
                    set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NEW.creationDate),INTERVAL DAY(LAST_DAY(NEW.creationDate))-1 DAY);
                    set @lastOfCurrentMonth = LAST_DAY(NEW.creationDate);
                    set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                    set @accommodation = NEW.accommodation;
                    set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(NEW.creationDate));
                    set @awards = IF(@awards >= 1, 5, 0);

                    set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                    set @penaltiesRanking = IF(@penalties > 0, 5, 0);

                    IF @exists = 0 THEN
                         INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,rankingPoints, awards, penalties)
                         VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @rankingPoints, @awards, 5);
                    ELSE
                         UPDATE ownership_ranking_extra rank
                         SET rank.penalties = 5,
                             rank.awards = @awards
                         WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                    END IF;
                END;
        ");

        $this->addSql("
                CREATE TRIGGER penalty_after_delete_trigger AFTER DELETE ON penalty
                  FOR EACH ROW
                BEGIN
                    set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(OLD.creationDate),INTERVAL DAY(LAST_DAY(OLD.creationDate))-1 DAY);
                    set @lastOfCurrentMonth = LAST_DAY(OLD.creationDate);
                    set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                    set @accommodation = OLD.accommodation;
                    set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(OLD.creationDate));
                    set @awards = IF(@awards >= 1, 5, 0);

                    set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                    set @penalties = (SELECT COUNT(*) FROM penalty
                                   WHERE creationDate <= CURDATE() AND finalizationDate >= CURDATE()
                                   AND accommodation = @accommodation);

                    set @penaltiesRanking = IF(@penalties > 0, 5, 0);

                    IF @exists = 0 THEN
                         INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,rankingPoints, awards, penalties)
                         VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @rankingPoints, @awards, @penaltiesRanking);
                    ELSE
                         UPDATE ownership_ranking_extra rank
                         SET rank.penalties = @penaltiesRanking,
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
        $this->addSql("DROP TRIGGER IF EXISTS penalty_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS penalty_after_delete_trigger");

    }
}
