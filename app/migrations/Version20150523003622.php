<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Creating report Daily in-place Clients Counting
 */
class Version20150523003622 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("insert into report(report_category, report_name, report_route_name, report_excel_export_route_name) values ((select max(nom_id) from nomenclator where nom_name = 'clients'),'Clientes en un día', 'mycp_reports_daily_in_place_clients', 'mycp_reports_daily_in_place_clients_excel')");
        $this->addSql("insert into reportparameter(parameter_type, parameter_report, parameter_name) values ((select max(nom_id) from nomenclator where nom_name = 'date'),(select max(report_id) from report where report_route_name = 'mycp_reports_daily_in_place_clients'), 'date')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("delete rp from reportparameter rp join report r on rp.parameter_report = r.report_id  where r.report_route_name = 'mycp_reports_daily_in_place_clients'");
        $this->addSql("delete r from report r where r.report_route_name = 'mycp_reports_daily_in_place_clients'");

    }
}
