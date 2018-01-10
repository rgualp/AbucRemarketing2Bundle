<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180209171934 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("delete pa_reservation_detail from pa_reservation_detail
join generalreservation on generalreservation.gen_res_id=pa_reservation_detail.reservationDetail
join user on user.user_id=generalreservation.gen_res_user_id
 where 
pa_reservation_detail.reservationDetail!=145524
 and pa_reservation_detail.reservationDetail!=145347
  and pa_reservation_detail.reservationDetail!=140335
   and pa_reservation_detail.reservationDetail!=124655
	 and pa_reservation_detail.reservationDetail!=17985
	  and pa_reservation_detail.reservationDetail!=17984
and user.user_role='ROLE_CLIENT_PARTNER' ");

        $this->addSql("delete ownershipreservation from ownershipreservation 
join generalreservation on generalreservation.gen_res_id=ownershipreservation.own_res_gen_res_id
join user on user.user_id=generalreservation.gen_res_user_id
where 
ownershipreservation.own_res_gen_res_id!=145524
 and ownershipreservation.own_res_gen_res_id!=145347 
 and ownershipreservation.own_res_gen_res_id!=140335 
 and ownershipreservation.own_res_gen_res_id!=124655 
 and ownershipreservation.own_res_gen_res_id!=17985 
 and ownershipreservation.own_res_gen_res_id!=17984
 and user.user_role='ROLE_CLIENT_PARTNER' ");
        $this->addSql("delete offerlog from offerlog 
join generalreservation on generalreservation.gen_res_id=offerlog.log_offer_reservation or offerlog.log_from_reservation=generalreservation.gen_res_id
join user on user.user_id=generalreservation.gen_res_user_id
where 
generalreservation.gen_res_id!=145524
 and generalreservation.gen_res_id!=145347 
 and generalreservation.gen_res_id!=140335 
 and generalreservation.gen_res_id!=124655 
 and generalreservation.gen_res_id!=17985 
 and generalreservation.gen_res_id!=17984
 and user.user_role='ROLE_CLIENT_PARTNER' ");

        $this->addSql("delete pending_payment from pending_payment
join generalreservation on generalreservation.gen_res_id=pending_payment.reservation
join user on user.user_id=generalreservation.gen_res_user_id

 where 
generalreservation.gen_res_id!=145524
 and generalreservation.gen_res_id!=145347
  and generalreservation.gen_res_id!=140335
   and generalreservation.gen_res_id!=124655
	 and generalreservation.gen_res_id!=17985
	  and generalreservation.gen_res_id!=17984
and user.user_role='ROLE_CLIENT_PARTNER' ");

        $this->addSql("delete notification from notification
join generalreservation on generalreservation.gen_res_id=notification.reservation
join user on user.user_id=generalreservation.gen_res_user_id

 where 
generalreservation.gen_res_id!=145524
 and generalreservation.gen_res_id!=145347
  and generalreservation.gen_res_id!=140335
   and generalreservation.gen_res_id!=124655
	 and generalreservation.gen_res_id!=17985
	  and generalreservation.gen_res_id!=17984
and user.user_role='ROLE_CLIENT_PARTNER' ");

        $this->addSql("delete failure from failure join 
generalreservation on generalreservation.gen_res_id=failure.reservation

join user on user.user_id=generalreservation.gen_res_user_id

 where 
generalreservation.gen_res_id!=145524
 and generalreservation.gen_res_id!=145347
  and generalreservation.gen_res_id!=140335
   and generalreservation.gen_res_id!=124655
	 and generalreservation.gen_res_id!=17985
	  and generalreservation.gen_res_id!=17984
and user.user_role='ROLE_CLIENT_PARTNER'");

 $this->addSql("delete generalreservation from generalreservation

join user on user.user_id=generalreservation.gen_res_user_id

 where 
generalreservation.gen_res_id!=145524
 and generalreservation.gen_res_id!=145347
  and generalreservation.gen_res_id!=140335
   and generalreservation.gen_res_id!=124655
	 and generalreservation.gen_res_id!=17985
	  and generalreservation.gen_res_id!=17984
and user.user_role='ROLE_CLIENT_PARTNER' ");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //Deleting rolepermissions
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_list_reservations_ag_reserved')");

        //Deleting permissions
        $this->addSql("delete from permission where perm_description = 'Inserción múltiple - Listar' and perm_category = 'Propiedades' and perm_route = 'mycp_list_reservations_ag_reserved'");
    }
}
