<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * If all ownershipReservations associated to a generalREservation are cancelled, then the status of the generalReservation must be set to cancelled
 */
class Version20150303193333 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        /*If all ownershipReservations associated to a generalREservation are cancelled, then the status of the generalReservation must be set to cancelled*/

        $this->addSql("update generalreservation gr
                       set gr.gen_res_status =  6
                       where (select count(*) from ownershipreservation ow where ow.own_res_status = 4
                       and ow.own_res_gen_res_id = gr.gen_res_id) = (select count(*) from ownershipreservation ow1
                                                                     where ow1.own_res_gen_res_id = gr.gen_res_id)"
                      );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
