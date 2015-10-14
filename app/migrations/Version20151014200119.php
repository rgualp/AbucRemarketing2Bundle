<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151014200119 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("insert into permission(perm_description, perm_category, perm_route) values
                        ('Check-in', 'Reservas ', 'mycp_list_reservations_checkin')");

        $this->addSql("insert rolepermission (rp_role, rp_permission) values
                    ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_list_reservations_checkin')),
                    ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM'), (select max(perm_id) from permission where perm_route = 'mycp_list_reservations_checkin')),
                    ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM_AND_ACCOMODATIONS'), (select max(perm_id) from permission where perm_route = 'mycp_list_reservations_checkin')),
                    ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_HOUSES_RESERVATIONS'), (select max(perm_id) from permission where perm_route = 'mycp_list_reservations_checkin')),
                    ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_HOUSES_RESERVATIONS_COMMENTS'), (select max(perm_id) from permission where perm_route = 'mycp_list_reservations_checkin'))");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //Deleting rolepermissions
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_list_reservations_checkin')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_list_reservations_checkin')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM_AND_ACCOMODATIONS') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_list_reservations_checkin')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_HOUSES_RESERVATIONS') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_list_reservations_checkin')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_HOUSES_RESERVATIONS_COMMENTS') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_list_reservations_checkin')");


        //Deleting permissions
        $this->addSql("delete from permission where perm_category = 'Reservas' and perm_route = 'mycp_list_reservations_checkin'");
    }
}
