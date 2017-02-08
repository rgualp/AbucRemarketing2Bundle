<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170207151022 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE ownership JOIN (SELECT ownership.own_id, MAX(payment.created) created FROM ownership INNER JOIN room ON (ownership.own_id = room.room_ownership)
INNER JOIN ownershipreservation ON (room.room_id = ownershipreservation.own_res_selected_room_id)
INNER JOIN booking ON (ownershipreservation.own_res_reservation_booking = booking.booking_id)
INNER JOIN payment ON (booking.booking_id = payment.booking_id)
GROUP BY ownership.own_id ORDER BY payment.created DESC) T ON T.own_id = ownership.own_id
SET own_hot_date = T.created WHERE ownership.own_id = T.own_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
