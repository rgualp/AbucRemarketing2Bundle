<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161221160140 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('accommodationModality','parameterType')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description)
                       values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'accommodationModality'), 'Modalidad')");

        $this->addSql("insert into reportparameter(parameter_type, parameter_report, parameter_name, parameter_order)
                       values ((select max(nom_id) from nomenclator where nom_name = 'accommodationModality'),(select max(report_id) from report where report_route_name = 'mycp_reports_clients_facturation_summary_daily'), 'Modalidad', 2)");

        $this->addSql("insert into reportparameter(parameter_type, parameter_report, parameter_name, parameter_order)
                       values ((select max(nom_id) from nomenclator where nom_name = 'accommodationModality'),(select max(report_id) from report where report_route_name = 'mycp_reports_clients_facturation_summary_monthly'), 'Modalidad', 2)");

        $this->addSql("insert into reportparameter(parameter_type, parameter_report, parameter_name, parameter_order)
                       values ((select max(nom_id) from nomenclator where nom_name = 'accommodationModality'),(select max(report_id) from report where report_route_name = 'mycp_reports_clients_facturation_summary_yearly'), 'Modalidad', 1)");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql("delete rp from reportparameter rp
                       join report r on rp.parameter_report = r.report_id
                       join nomenclator n on rp.parameter_type = n.nom_id
                       where r.report_route_name = 'mycp_reports_clients_facturation_summary_daily' and n.nom_name='accommodationModality'");

        $this->addSql("delete rp from reportparameter rp
                       join report r on rp.parameter_report = r.report_id
                       join nomenclator n on rp.parameter_type = n.nom_id
                       where r.report_route_name = 'mycp_reports_clients_facturation_summary_monthly' and n.nom_name='accommodationModality'");

        $this->addSql("delete rp from reportparameter rp
                       join report r on rp.parameter_report = r.report_id
                       join nomenclator n on rp.parameter_type = n.nom_id
                       where r.report_route_name = 'mycp_reports_clients_facturation_summary_yearly' and n.nom_name='accommodationModality'");

        $this->addSql("delete nlang
                      from nomenclatorlang nlang
                      join nomenclator nom on nlang.nom_lang_id_nomenclator = nom.nom_id
                      where nom.nom_category LIKE 'parameterType' and nom.nom_name = 'accommodationModality'");

        $this->addSql("delete nom from nomenclator nom where nom.nom_category LIKE 'parameterType' and nom.nom_name = 'accommodationModality'");

    }
}
