<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161205175324 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //Triggers en ownership
        $this->addSql("
                CREATE TRIGGER accommodation_calendar_frequency_after_insert_trigger AFTER INSERT ON accommodation_calendar_frequency
                  FOR EACH ROW
                BEGIN
                    set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NEW.updatedDate),INTERVAL DAY(LAST_DAY(NEW.updatedDate))-1 DAY);
                    set @lastOfCurrentMonth = LAST_DAY(NEW.updatedDate);
                    set @currentMonth = MONTH(NEW.updatedDate);
                    set @accommodation = NEW.accommodation;

                    set @total = (select COUNT(*) FROM accommodation_calendar_frequency freq where MONTH(freq.updatedDate) = @currentMonth and freq.accommodation = @accommodation);

                    set @fadEvaluation = 0;
                    IF @total > 0 AND @total < 4 THEN
                     set @fadEvaluation = 1;
                    ELSEIF @total >= 4 AND @total < 25 THEN
                     set @fadEvaluation = 3;
                    ELSE
                      set @fadEvaluation = 5;
                    END IF;

                    set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                    set @rr = (SELECT o.own_inmediate_booking FROM ownership o WHERE o.own_id = @accommodation LIMIT 1);
                    set @ri = (SELECT o.own_inmediate_booking_2 FROM ownership o WHERE o.own_id = @accommodation LIMIT 1);
                    set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(NEW.updatedDate));
                    set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);

                    IF @exists = 0 THEN
                        INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate, fad, rr, ri, awards, rankingPoints)
                        VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @fadEvaluation, IF(@rr > 0, 5, 0), IF(@ri > 0, 5, 0), IF(@awards > 0, 5, 0), @rankingPoints);
                    ELSE
                        UPDATE ownership_ranking_extra rank
                        SET rank.fad = @fadEvaluation,
                            rank.rr = @rr,
                            rank.ri = @ri,
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

    }
}
