<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160920142459 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("insert into permission(perm_description, perm_category, perm_route) values
('Listado de bookings de agencia', 'Agencia', 'mycp_list_reservations_ag_booking'),
('Detalles de booking de agencia', 'Agencia', 'mycp_details_reservations_ag_booking'),
('Listar reservaciones de agencia', 'Agencia', 'mycp_list_reservations_ag'),
('Listar check-in de agencia', 'Agencia', 'mycp_list_agency_checkin'),
('Listar agencias', 'Agencia', 'mycp_list_agency'),
('Detalles de la agencia', 'Agencia', 'mycp_details_agency'),
('Activar agencia', 'Agencia', 'mycp_enable_agency'),
('Editar agencia', 'Agencia', 'mycp_edit_agency')");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
