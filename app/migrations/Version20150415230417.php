<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150415230417 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("insert into permission(perm_description, perm_category, perm_route) values ('Inserción múltiple - Listar', 'Propiedades', 'mycp_batch_process_ownership'),('Inserción múltiple - Procesar', 'Propiedades', 'mycp_batch_process_insert_ownership'),('Inserción múltiple - Detalles', 'Propiedades', 'mycp_batch_view_ownership')");
        $this->addSql("insert rolepermission (rp_role, rp_permission)
            values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_batch_process_ownership')),
            ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_batch_process_insert_ownership')),
            ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_batch_view_ownership'))");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //Deleting rolepermissions
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_batch_process_ownership')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_batch_process_insert_ownership')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_batch_view_ownership')");

        //Deleting permissions
        $this->addSql("delete from permission where perm_description = 'Inserción múltiple - Listar' and perm_category = 'Propiedades' and perm_route = 'mycp_batch_process_ownership'");
        $this->addSql("delete from permission where perm_description = 'Inserción múltiple - Procesar' and perm_category = 'Propiedades' and perm_route = 'mycp_batch_process_insert_ownership'");
        $this->addSql("delete from permission where perm_description = 'Inserción múltiple - Detalles' and perm_category = 'Propiedades' and perm_route = 'mycp_batch_view_ownership'");
    }
}
