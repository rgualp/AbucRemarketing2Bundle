<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160706210415 extends AbstractMigration
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
                CREATE TRIGGER ownership_after_insert_trigger AFTER INSERT ON ownership
                  FOR EACH ROW
                BEGIN
                    insert into ownershipdata (accommodation) VALUES(NEW.own_id);
                END;
        ");

        $this->addSql("
                CREATE TRIGGER ownership_before_delete_trigger BEFORE DELETE ON ownership
                  FOR EACH ROW
                BEGIN
                    delete from ownershipdata where accommodation = OLD.own_id;
                END;
        ");

        //Triggers en rooms
        $this->addSql("
                CREATE TRIGGER room_after_insert_trigger AFTER INSERT ON room
                  FOR EACH ROW
                BEGIN
                    IF NEW.room_active = 1 THEN
                      UPDATE ownershipdata
                      SET activeRooms = activeRooms + 1
                      WHERE accommodation = NEW.room_ownership;
                    END IF;
                END;
        ");

        $this->addSql("
                CREATE TRIGGER room_after_update_trigger AFTER UPDATE ON room
                  FOR EACH ROW
                BEGIN
                    IF OLD.room_active = 0 AND NEW.room_active = 1 THEN
                      UPDATE ownershipdata
                      SET activeRooms = activeRooms + 1
                      WHERE accommodation = NEW.room_ownership;
                    ELSEIF OLD.room_active = 1 AND NEW.room_active = 0 THEN
                        UPDATE ownershipdata
                          SET activeRooms = activeRooms - 1
                          WHERE accommodation = NEW.room_ownership;
                    END IF;
                END;
        ");

        $this->addSql("
                CREATE TRIGGER room_after_delete_trigger AFTER DELETE ON room
                  FOR EACH ROW
                BEGIN
                    IF OLD.room_active = 1 THEN
                      UPDATE ownershipdata
                      SET activeRooms = activeRooms - 1
                      WHERE accommodation = OLD.room_ownership;
                    END IF;
                END;
        ");

        //Triggers en comments
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

        //Triggers en photo
        $this->addSql("
                CREATE TRIGGER photo_after_insert_trigger AFTER INSERT ON photo
                  FOR EACH ROW
                BEGIN
                      UPDATE ownershipdata
                      SET principalPhoto = (SELECT opho.own_pho_id FROM ownershipphoto opho join photo pho on pho.pho_id = opho.own_pho_pho_id
                                            WHERE opho.own_pho_own_id = accommodation ORDER BY pho.pho_order ASC LIMIT 1)
                      WHERE accommodation = (SELECT MIN(op.own_pho_own_id) FROM ownershipphoto op WHERE op.own_pho_pho_id = NEW.pho_id);
                END;
        ");

        $this->addSql("
                CREATE TRIGGER photo_after_update_trigger AFTER UPDATE ON photo
                  FOR EACH ROW
                BEGIN
                    UPDATE ownershipdata
                    SET principalPhoto = (SELECT opho.own_pho_id FROM ownershipphoto opho join photo pho on pho.pho_id = opho.own_pho_pho_id
                                            WHERE opho.own_pho_own_id = accommodation ORDER BY pho.pho_order ASC LIMIT 1)
                    WHERE accommodation = (SELECT MIN(op.own_pho_own_id) FROM ownershipphoto op WHERE op.own_pho_pho_id = NEW.pho_id);
                END;
        ");

        $this->addSql("
                CREATE TRIGGER photo_after_delete_trigger AFTER DELETE ON photo
                  FOR EACH ROW
                BEGIN
                    UPDATE ownershipdata
                    SET principalPhoto = (SELECT opho.own_pho_id FROM ownershipphoto opho join photo pho on pho.pho_id = opho.own_pho_pho_id
                                            WHERE opho.own_pho_own_id = accommodation ORDER BY pho.pho_order ASC LIMIT 1)
                    WHERE accommodation = (SELECT MIN(op.own_pho_own_id) FROM ownershipphoto op WHERE op.own_pho_pho_id = OLD.pho_id);
                END;
        ");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("DROP TRIGGER IF EXISTS ownership_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS ownership_before_delete_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS room_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS room_after_update_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS room_after_delete_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS comment_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS comment_after_update_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS comment_after_delete_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS ownershipreservation_after_update_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS photo_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS photo_after_update_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS photo_after_delete_trigger");

    }
}
