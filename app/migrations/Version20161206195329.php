<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161206195329 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS accommodation_award_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS accommodation_award_after_delete_trigger");

        $this->addSql("
                CREATE TRIGGER accommodation_award_after_insert_trigger AFTER INSERT ON accommodation_award
                  FOR EACH ROW
                BEGIN
                      set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NOW()),INTERVAL DAY(LAST_DAY(NOW()))-1 DAY);
                      set @lastOfCurrentMonth = LAST_DAY(NOW());
                      set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                      set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(NOW()));
                      set @awards = IF(@awards >= 1, 5, 0);
                      set @penalties = (SELECT COUNT(*) FROM penalty WHERE creationDate <= CURDATE() AND finalizationDate >= CURDATE() AND accommodation = @accommodation);
                      set @penaltiesRanking = IF(@penalties > 0, 5, 0);
                      set @accommodation = NEW.accommodation;

                      set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                      IF @exists = 0 THEN
                            INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,rankingPoints, awards, penalties)
                            VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @rankingPoints, 5, @penaltiesRanking);
                      ELSE
                            UPDATE ownership_ranking_extra rank
                            SET rank.awards = 5,
                                rank.penalties = @penaltiesRanking
                            WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                      END IF;
                END;
        ");

        $this->addSql("
                CREATE TRIGGER accommodation_award_after_delete_trigger AFTER DELETE ON accommodation_award
                  FOR EACH ROW
                BEGIN
                      set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NOW()),INTERVAL DAY(LAST_DAY(NOW()))-1 DAY);
                      set @lastOfCurrentMonth = LAST_DAY(NOW());
                      set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                      set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(NOW()));
                      set @awards = IF(@awards >= 1, 5, 0);
                      set @penalties = (SELECT COUNT(*) FROM penalty WHERE creationDate <= CURDATE() AND finalizationDate >= CURDATE() AND accommodation = @accommodation);
                      set @penaltiesRanking = IF(@penalties > 0, 5, 0);
                      set @accommodation = OLD.accommodation;

                      set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                      IF @exists = 0 THEN
                            INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,rankingPoints, awards, penalties)
                            VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @rankingPoints, @awards, @penaltiesRanking);
                        ELSE
                            UPDATE ownership_ranking_extra rank
                            SET rank.awards = @awards,
                                rank.penalties = @penaltiesRanking
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
        $this->addSql("DROP TRIGGER IF EXISTS accommodation_award_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS accommodation_award_after_delete_trigger");
    }
}
