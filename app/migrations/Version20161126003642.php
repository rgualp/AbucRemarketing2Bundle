<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161126003642 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("insert into permission(perm_description, perm_category, perm_route) values
                        ('Listar penalizaciones', 'Penalizaciones', 'mycp_list_penalties'),
                        ('Insertar penalización', 'Penalizaciones', 'mycp_create_penalty'),
                        ('Editar penalización', 'Penalizaciones', 'mycp_delete_penalty')");


        $this->addSql("insert rolepermission (rp_role, rp_permission)
            values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_list_penalties')),
            ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_create_penalty')),
            ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_delete_penalty'))");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //Deleting rolepermissions
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_list_penalties')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_create_penalty')");
        $this->addSql("delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF') and rp_permission = (select max(perm_id) from permission where perm_route = 'mycp_delete_penalty')");

        //Deleting permissions
        $this->addSql("delete from permission where perm_description = 'Listar penalizaciones' and perm_category = 'Penalizaciones' and perm_route = 'mycp_list_penalties'");
        $this->addSql("delete from permission where perm_description = 'Insertar penalización' and perm_category = 'Penalizaciones' and perm_route = 'mycp_create_penalty'");
        $this->addSql("delete from permission where perm_description = 'Editar penalización' and perm_category = 'Penalizaciones' and perm_route = 'mycp_delete_penalty'");

    }
}
