<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161128180414 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("insert into permission(perm_description, perm_category, perm_route) values
                        ('Listar fallos', 'Fallos', 'mycp_list_touristfailures'),
                        ('Insertar fallo', 'Fallos', 'mycp_create_touristfailures'),
                        ('Eliminar fallo', 'Fallos', 'mycp_delete_touristfailures')");


        $this->addSql("insert rolepermission (rp_role, rp_permission)
            values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_list_touristfailures')),
            ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_create_touristfailures')),
            ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_delete_touristfailures'))");

        $this->addSql("insert rolepermission (rp_role, rp_permission)
            values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM'), (select max(perm_id) from permission where perm_route = 'mycp_list_touristfailures')),
            ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM'), (select max(perm_id) from permission where perm_route = 'mycp_create_touristfailures')),
            ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM'), (select max(perm_id) from permission where perm_route = 'mycp_delete_touristfailures'))");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //Deleting rolepermissions
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_list_touristfailures')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_create_touristfailures')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_delete_touristfailures')");

        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_list_touristfailures')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_create_touristfailures')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_delete_touristfailures')");

        //Deleting permissions
        $this->addSql("delete from permission where perm_description = 'Listar fallos' and perm_category = 'Fallos' and perm_route = 'mycp_list_touristfailures'");
        $this->addSql("delete from permission where perm_description = 'Insertar fallo' and perm_category = 'Fallos' and perm_route = 'mycp_create_touristfailures'");
        $this->addSql("delete from permission where perm_description = 'Eliminar fallo' and perm_category = 'Fallos' and perm_route = 'mycp_delete_touristfailures'");

    }
}
