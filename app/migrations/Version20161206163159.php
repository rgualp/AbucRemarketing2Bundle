<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161206163159 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS comment_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS comment_after_update_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS comment_after_delete_trigger");

        $this->addSql("
                CREATE TRIGGER comment_after_insert_trigger AFTER INSERT ON comment
                  FOR EACH ROW
                BEGIN
                    IF NEW.com_public = 1 THEN
                      UPDATE ownershipdata
                      SET publishedComments = publishedComments + 1
                      WHERE accommodation = NEW.com_ownership;

                      set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NEW.com_date),INTERVAL DAY(LAST_DAY(NEW.com_date))-1 DAY);
                      set @lastOfCurrentMonth = LAST_DAY(NEW.com_date);
                      set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                      set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(NEW.com_date));
                      set @awards = IF(@awards >= 1, 5, 0);
                      set @accommodation = NEW.com_ownership;

                      set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                      set @total = (SELECT COUNT(*) FROM comment c WHERE c.com_date >= @firstOfCurrentMonth AND c.com_date <= @lastOfCurrentMonth AND c.com_ownership = @accommodation AND c.com_public = 1 AND c.com_user IN (select distinct gres.gen_res_user_id from generalReservation gres where gres.gen_res_status = 2));
                      set @positive = (SELECT COUNT(*) FROM comment c WHERE c.com_date >= @firstOfCurrentMonth AND c.com_date <= @lastOfCurrentMonth AND c.com_ownership = @accommodation AND c.positive = 1 and c.com_public = 1 AND c.com_user IN (select distinct gres.gen_res_user_id from generalReservation gres where gres.gen_res_status = 2));

                      set @negative = @total - @positive;

                      set @positivePercent = COALESCE((@positive / @total) * 100, 0);
                      set @negativePercent = COALESCE((@negative / @total) * 100, 0);

                      set @positiveRanking = IF(@total = 0, 0, IF(@positivePercent = 100, 100, IF(@total = 1, 1, 3)));
                      set @negativeRanking = IF(@negative = 0, 0, IF(@negativePercent >= 50, 5, IF(@negativePercent < 33, 1, 3)));

                      IF @exists = 0 THEN
                            INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,positiveComments, negativeComments,rankingPoints, awards)
                            VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @positiveRanking, @negativeRanking, @rankingPoints, @awards);
                        ELSE
                            UPDATE ownership_ranking_extra rank
                            SET rank.positiveComments = @positiveRanking,
                                rank.negativeComments = @negativeRanking,
                                rank.awards = @awards
                            WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                        END IF;

                    END IF;
                END;
        ");

        $this->addSql("
                CREATE TRIGGER comment_after_update_trigger AFTER UPDATE ON comment
                  FOR EACH ROW
                BEGIN
                    set @publicChanged = 0;

                    IF OLD.com_public = 0 AND NEW.com_public = 1 THEN
                      UPDATE ownershipdata
                      SET publishedComments = publishedComments + 1
                      WHERE accommodation = NEW.com_ownership;

                      set @publicChanged = 1;
                    ELSEIF OLD.com_public = 1 AND NEW.com_public = 0 THEN
                        UPDATE ownershipdata
                          SET publishedComments = publishedComments - 1
                          WHERE accommodation = NEW.com_ownership;

                          set @publicChanged = 1;
                     ELSEIF NEW.com_public = 1 AND OLD.positive != NEW.positive THEN
                          set @publicChanged = 1;
                    END IF;

                    IF @publicChanged = 1 THEN
                          set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NEW.com_date),INTERVAL DAY(LAST_DAY(NEW.com_date))-1 DAY);
                          set @lastOfCurrentMonth = LAST_DAY(NEW.com_date);
                          set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                          set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(NEW.com_date));
                          set @awards = IF(@awards >= 1, 5, 0);
                          set @accommodation = NEW.com_ownership;

                          set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                          set @total = (SELECT COUNT(*) FROM comment c WHERE c.com_date >= @firstOfCurrentMonth AND c.com_date <= @lastOfCurrentMonth AND c.com_ownership = @accommodation AND c.com_public = 1 AND c.com_user IN (select distinct gres.gen_res_user_id from generalReservation gres where gres.gen_res_status = 2));
                          set @positive = (SELECT COUNT(*) FROM comment c WHERE c.com_date >= @firstOfCurrentMonth AND c.com_date <= @lastOfCurrentMonth AND c.com_ownership = @accommodation AND c.positive = 1 and c.com_public = 1 AND c.com_user IN (select distinct gres.gen_res_user_id from generalReservation gres where gres.gen_res_status = 2));

                          set @negative = @total - @positive;

                          set @positivePercent = COALESCE((@positive / @total) * 100, 0);
                          set @negativePercent = COALESCE((@negative / @total) * 100, 0);

                          set @positiveRanking = IF(@total = 0, 0, IF(@positivePercent = 100, 100, IF(@total = 1, 1, 3)));
                          set @negativeRanking = IF(@negative = 0, 0, IF(@negativePercent >= 50, 5, IF(@negativePercent < 33, 1, 3)));

                          IF @exists = 0 THEN
                                INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,positiveComments, negativeComments,rankingPoints, awards)
                                VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @positiveRanking, @negativeRanking, @rankingPoints, @awards);
                            ELSE
                                UPDATE ownership_ranking_extra rank
                                SET rank.positiveComments = @positiveRanking,
                                    rank.negativeComments = @negativeRanking,
                                    rank.awards = @awards
                                WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                            END IF;
                        END IF;
                END;
        ");

        $this->addSql("
                CREATE TRIGGER comment_after_delete_trigger AFTER DELETE ON comment
                  FOR EACH ROW
                BEGIN
                    IF OLD.com_public = 1 THEN
                      UPDATE ownershipdata
                      SET publishedComments = publishedComments - 1
                      WHERE accommodation = OLD.com_ownership;

                      set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(OLD.com_date),INTERVAL DAY(LAST_DAY(OLD.com_date))-1 DAY);
                      set @lastOfCurrentMonth = LAST_DAY(OLD.com_date);
                      set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);
                      set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = @accommodation AND aa.year = YEAR(OLD.com_date));
                      set @awards = IF(@awards >= 1, 5, 0);
                      set @accommodation = OLD.com_ownership;

                      set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                      set @total = (SELECT COUNT(*) FROM comment c WHERE c.com_date >= @firstOfCurrentMonth AND c.com_date <= @lastOfCurrentMonth AND c.com_ownership = @accommodation AND c.com_public = 1 AND c.com_user IN (select distinct gres.gen_res_user_id from generalReservation gres where gres.gen_res_status = 2));
                      set @positive = (SELECT COUNT(*) FROM comment c WHERE c.com_date >= @firstOfCurrentMonth AND c.com_date <= @lastOfCurrentMonth AND c.com_ownership = @accommodation AND c.positive = 1 and c.com_public = 1 AND c.com_user IN (select distinct gres.gen_res_user_id from generalReservation gres where gres.gen_res_status = 2));

                      set @negative = @total - @positive;

                      set @positivePercent = COALESCE((@positive / @total) * 100, 0);
                      set @negativePercent = COALESCE((@negative / @total) * 100, 0);

                      set @positiveRanking = IF(@total = 0, 0, IF(@positivePercent = 100, 100, IF(@total = 1, 1, 3)));
                      set @negativeRanking = IF(@negative = 0, 0, IF(@negativePercent >= 50, 5, IF(@negativePercent < 33, 1, 3)));

                      IF @exists = 0 THEN
                            INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,positiveComments, negativeComments,rankingPoints, awards)
                            VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @positiveRanking, @negativeRanking, @rankingPoints, @awards);
                      ELSE
                            UPDATE ownership_ranking_extra rank
                            SET rank.positiveComments = @positiveRanking,
                                rank.negativeComments = @negativeRanking,
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
        $this->addSql("DROP TRIGGER IF EXISTS comment_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS comment_after_update_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS comment_after_delete_trigger");

        $this->addSql("
                CREATE TRIGGER comment_after_insert_trigger AFTER INSERT ON comment
                  FOR EACH ROW
                BEGIN
                    IF NEW.com_public = 1 THEN
                      UPDATE ownershipdata
                      SET publishedComments = publishedComments + 1
                      WHERE accommodation = NEW.com_ownership;
                    END IF;
                END;
        ");

        $this->addSql("
                CREATE TRIGGER comment_after_update_trigger AFTER UPDATE ON comment
                  FOR EACH ROW
                BEGIN
                    IF OLD.com_public = 0 AND NEW.com_public = 1 THEN
                      UPDATE ownershipdata
                      SET publishedComments = publishedComments + 1
                      WHERE accommodation = NEW.com_ownership;
                    ELSEIF OLD.com_public = 1 AND NEW.com_public = 0 THEN
                        UPDATE ownershipdata
                          SET publishedComments = publishedComments - 1
                          WHERE accommodation = NEW.com_ownership;
                    END IF;
                END;
        ");

        $this->addSql("
                CREATE TRIGGER comment_after_delete_trigger AFTER DELETE ON comment
                  FOR EACH ROW
                BEGIN
                    IF OLD.com_public = 1 THEN
                      UPDATE ownershipdata
                      SET publishedComments = publishedComments - 1
                      WHERE accommodation = OLD.com_ownership;
                    END IF;
                END;
        ");


    }
}
