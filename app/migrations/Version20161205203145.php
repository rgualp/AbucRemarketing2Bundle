<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161205203145 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS ownership_after_insert_trigger");

        //Triggers en ownership
        $this->addSql("
                CREATE TRIGGER ownership_after_insert_trigger AFTER INSERT ON ownership
                  FOR EACH ROW
                BEGIN
                    insert into ownershipdata (accommodation) VALUES(NEW.own_id);
                    set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NOW()),INTERVAL DAY(LAST_DAY(NOW()))-1 DAY);
                    set @lastOfCurrentMonth = LAST_DAY(NOW());
                    set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);

                    IF NEW.own_inmediate_booking = 1 THEN
                        INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,rr,rankingPoints)
                        VALUES (NEW.own_id,@firstOfCurrentMonth,@lastOfCurrentMonth, 5, @rankingPoints);
                    ELSEIF NEW.own_inmediate_booking_2 = 1 THEN
                        INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,ri,rankingPoints)
                        VALUES (NEW.own_id,@firstOfCurrentMonth,@lastOfCurrentMonth, 5, @rankingPoints);
                    END IF;
                END;
        ");

        $this->addSql("DROP TRIGGER IF EXISTS ownership_after_update_trigger");

        //Triggers en ownership
        $this->addSql("
                CREATE TRIGGER ownership_after_update_trigger AFTER UPDATE ON ownership
                  FOR EACH ROW
                BEGIN
                    set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(NOW()),INTERVAL DAY(LAST_DAY(NOW()))-1 DAY);
                    set @lastOfCurrentMonth = LAST_DAY(NOW());
                    set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);

                    set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = NEW.own_id AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

                    IF NEW.own_inmediate_booking = 1 AND OLD.own_inmediate_booking = 0 THEN
                        IF @exists = 0 THEN
                            INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,rr, ri,rankingPoints)
                            VALUES (NEW.own_id,@firstOfCurrentMonth,@lastOfCurrentMonth, 5, 0, @rankingPoints);
                        ELSE
                            UPDATE ownership_ranking_extra rank
                            SET rank.rr = 5,
                                rank.ri = 0
                            WHERE rank.accommodation = NEW.own_id AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                        END IF;
                    ELSEIF NEW.own_inmediate_booking_2 = 1 AND OLD.own_inmediate_booking_2 = 0  THEN
                        IF @exists = 0 THEN
                            INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,rr,ri,rankingPoints)
                            VALUES (NEW.own_id,@firstOfCurrentMonth,@lastOfCurrentMonth, 0, 5, @rankingPoints);
                        ELSE
                            UPDATE ownership_ranking_extra rank
                            SET rank.ri = 5,
                                rank.rr = 0
                            WHERE rank.accommodation = NEW.own_id AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
                        END IF;
                    END IF;
                END;
        ");



        $this->addSql("DROP TRIGGER IF EXISTS ownership_before_delete_trigger");
        $this->addSql("
                CREATE TRIGGER ownership_before_delete_trigger BEFORE DELETE ON ownership
                  FOR EACH ROW
                BEGIN
                    delete from ownershipdata where accommodation = OLD.own_id;
                    delete from ownership_ranking_extra where accommodation = OLD.own_id;
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
