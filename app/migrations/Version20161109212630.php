<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161109212630 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
                DROP TRIGGER IF EXISTS room_after_update_trigger;
                CREATE TRIGGER room_after_update_trigger AFTER UPDATE ON room
                  FOR EACH ROW
                BEGIN
                    update ownership own
                    set own.own_rooms_total = (SELECT count(r.room_id) FROM room r where r.room_ownership = NEW.room_ownership AND r.room_active = 1),
                    own.own_minimum_price = (SELECT min(r1.room_price_down_to) FROM room r1 where r1.room_ownership = NEW.room_ownership AND r1.room_active = 1),
                    own.own_maximum_price = (SELECT max(r2.room_price_up_to) FROM room r2 where r2.room_ownership = NEW.room_ownership AND r2.room_active = 1),
                    own.own_maximun_number_guests = (SELECT SUM(IF(r3.room_type LIKE '%individual%', 1, IF(r3.room_type LIKE '%doble%', 2, 3))) FROM room r3 where r3.room_ownership = NEW.room_ownership AND r3.room_active = 1)
                    WHERE own.own_id = NEW.room_ownership;

                    UPDATE ownershipdata
                    SET activeRooms = (SELECT count(r.room_id) FROM room r WHERE r.room_ownership = NEW.room_ownership AND r.room_active = 1)
                    WHERE accommodation = NEW.room_ownership;
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
