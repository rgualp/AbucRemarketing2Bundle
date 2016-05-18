<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160518163434 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("UPDATE report SET report_name='Reporte diario de facturación' WHERE report_route_name = 'mycp_reports_clients_facturation_summary_daily'");
        $this->addSql("UPDATE report SET report_name='Reporte mensual de facturación' WHERE report_route_name = 'mycp_reports_clients_facturation_summary_monthly'");
        $this->addSql("UPDATE report SET report_name='Reporte anual de facturación' WHERE report_route_name = 'mycp_reports_clients_facturation_summary_yearly'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("UPDATE report SET report_name='Reporte Diario de Clientes vs Solicitudes(Facturación)' WHERE report_route_name = 'mycp_reports_clients_facturation_summary_daily'");
        $this->addSql("UPDATE report SET report_name='Reporte Mensual de Clientes vs Solicitudes(Facturación)' WHERE report_route_name = 'mycp_reports_clients_facturation_summary_monthly'");
        $this->addSql("UPDATE report SET report_name='Reporte Anual de Clientes vs Solicitudes(Facturación)' WHERE report_route_name = 'mycp_reports_clients_facturation_summary_yearly'");


    }
}
