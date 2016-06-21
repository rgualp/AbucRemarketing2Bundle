<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160621202702 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("insert into permission(perm_description, perm_category, perm_route) values ('Listar pagos', 'Pagos - Alojamientos', 'mycp_list_payments'),
                        ('Insertar pago', 'Pagos - Alojamientos', 'mycp_new_payment'),
                        ('Editar pago', 'Pagos - Alojamientos', 'mycp_edit_payment'),
                        ('Eliminar pago', 'Pagos - Alojamientos', 'mycp_delete_payment'),
                        ('Listar alojamientos sin pago de inscripción', 'Pagos - Alojamientos', 'mycp_accommodations_no_payment')");


        $this->addSql("insert rolepermission (rp_role, rp_permission)
            values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_list_payments')),
            ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_new_payment')),
            ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_edit_payment')),
            ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_delete_payment')),
            ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_accommodations_no_payment'))");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //Deleting rolepermissions
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_list_payments')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_new_payment')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_edit_payment')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_delete_payment')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_accommodations_no_payment')");

        //Deleting permissions
        $this->addSql("delete from permission where perm_description = 'Listar pagos' and perm_category = 'Pagos - Alojamientos' and perm_route = 'mycp_list_payments'");
        $this->addSql("delete from permission where perm_description = 'Insertar pago' and perm_category = 'Pagos - Alojamientos' and perm_route = 'mycp_new_payment'");
        $this->addSql("delete from permission where perm_description = 'Editar pago' and perm_category = 'Pagos - Alojamientos' and perm_route = 'mycp_edit_payment'");
        $this->addSql("delete from permission where perm_description = 'Eliminar pago' and perm_category = 'Pagos - Alojamientos' and perm_route = 'mycp_delete_payment'");
        $this->addSql("delete from permission where perm_description = 'Listar alojamientos sin pago de inscripción' and perm_category = 'Pagos - Alojamientos' and perm_route = 'mycp_accommodations_no_payment'");

    }
}
