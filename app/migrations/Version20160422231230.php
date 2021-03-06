<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160422231230 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("insert into report(report_category, report_name, report_route_name, report_excel_export_route_name, published) values ((select max(nom_id) from nomenclator where nom_name = 'reservations'),'Reporte Mensual de Estado por Reservas', 'mycp_reports_reservations_summary', 'mycp_reports_reservations_summary_excel', 0)");
        $this->addSql("insert into reportparameter(parameter_type, parameter_report, parameter_name, parameter_order) values ((select max(nom_id) from nomenclator where nom_name = 'dateRange'),(select max(report_id) from report where report_route_name = 'mycp_reports_reservations_summary'), 'Rango de fechas', 1)");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("delete rp from reportparameter rp join report r on rp.parameter_report = r.report_id  where r.report_route_name = 'mycp_reports_reservations_summary'");
        $this->addSql("delete r from report r where r.report_route_name = 'mycp_reports_reservations_summary'");
    }
}
