<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170113203105 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('update booking b
                        set b.payAtService = (
                        SELECT SUM(owres.own_res_total_in_site)*(1 - o.own_commission_percent/100)
                        FROM ownershipreservation owres
                         JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                         JOIN ownership o on o.own_id = gres.gen_res_own_id
                         WHERE owres.own_res_reservation_booking = b.booking_id
                         group by owres.own_res_reservation_booking
                        );');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
