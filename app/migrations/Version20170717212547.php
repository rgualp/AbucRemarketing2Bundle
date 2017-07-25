<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170717212547 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("set @type = (SELECT min(nom_id) FROM nomenclator WHERE nom_name LIKE 'completePayment')");
        $this->addSql("set @user = (SELECT min(user_id) FROM user WHERE user_name LIKE 'yanetmr')");

        $this->addSql("INSERT INTO pending_payment (reservation, booking, payment_date, register_date, amount, type, user)
                       select gres.gen_res_id, b.booking_id, DATE_ADD(gres.gen_res_to_date, INTERVAL 2 day),gres.gen_res_date,
                        SUM(owres.own_res_total_in_site * (1 - o.own_commission_percent/100)), @type, @user
                        from generalreservation gres
                        join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
                        join booking b on b.booking_id = owres.own_res_reservation_booking
                        join ownership o on gres.gen_res_own_id = o.own_id
                        where owres.own_res_reservation_from_date >= '2017-01-01'
                        and owres.own_res_status = 5
                        and gres.gen_res_status = 2
                        group by owres.own_res_gen_res_id
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
