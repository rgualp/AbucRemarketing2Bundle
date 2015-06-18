<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adding Ownerships reports rows to the database!
 */
class Version20150618165116 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        /*Report categories*/
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('ownership','reportCategory')");

        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'ownership'), 'Propiedades')");

        /*Parameter types*/
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('location','parameterType')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('location_full','parameterType')");

        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'location'), 'Provincia-Municipio')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'location_full'), 'Provincia-Municipio-Destino')");

        $this->addSql("insert into report(report_category, report_name, report_route_name, report_excel_export_route_name) values ((select max(nom_id) from nomenclator where nom_name = 'ownership'),'Resumen propiedades', 'mycp_reports_ownership_general_stats', 'mycp_reports_ownership_general_stats_excel')");
        $this->addSql("insert into reportParameter(parameter_type, parameter_report, parameter_name) values ((select max(nom_id) from nomenclator where nom_name = 'location'),(select max(report_id) from report where report_route_name = 'mycp_reports_ownership_general_stats'), 'Localización')");

        $this->addSql("insert into report(report_category, report_name, report_route_name, report_excel_export_route_name) values ((select max(nom_id) from nomenclator where nom_name = 'ownership'),'Propiedades vs Reservas', 'mycp_reports_ownership_vsReservations_stats', 'mycp_reports_ownership_vsReservations_stats_excel')");
        $this->addSql("insert into reportParameter(parameter_type, parameter_report, parameter_name) values ((select max(nom_id) from nomenclator where nom_name = 'location_full'),(select max(report_id) from report where report_route_name = 'mycp_reports_ownership_vsReservations_stats'), 'Localización Full')");
        $this->addSql("insert into reportParameter(parameter_type, parameter_report, parameter_name) values ((select max(nom_id) from nomenclator where nom_name = 'dateRange'),(select max(report_id) from report where report_route_name = 'mycp_reports_ownership_vsReservations_stats'), 'Rango de fechas')");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("delete nlang from nomenclatorlang nlang join nomenclator nom on nlang.nom_lang_id_nomenclator = nom.nom_id where nom.nom_category LIKE 'parameterType' or nom.nom_category LIKE 'reportCategory'");
        $this->addSql("delete nom from nomenclator nom where nom.nom_category LIKE 'parameterType' or nom.nom_category LIKE 'reportCategory'");

        $this->addSql("delete rp from reportParameter rp join report r on rp.parameter_report = r.report_id  where r.report_route_name = 'mycp_reports_ownership_general_stats'");
        $this->addSql("delete r from report r where r.report_route_name = 'mycp_reports_ownership_general_stats'");
        $this->addSql("delete rp from reportParameter rp join report r on rp.parameter_report = r.report_id  where r.report_route_name = 'mycp_reports_ownership_vsReservations_stats'");
        $this->addSql("delete r from report r where r.report_route_name = 'mycp_reports_ownership_vsReservations_stats'");
    }
}
