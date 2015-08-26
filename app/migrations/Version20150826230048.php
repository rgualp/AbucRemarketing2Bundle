<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150826230048 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE reportparameter SET parameter_order = 1 WHERE parameter_name LIKE 'Rango de fechas' and parameter_report = (select min(report_id) from report where report_name LIKE 'Propiedades vs Reservas')");
        $this->addSql("UPDATE reportparameter SET parameter_order = 2 WHERE parameter_name LIKE 'Localización' and parameter_report = (select min(report_id) from report where report_name LIKE 'Propiedades vs Reservas')");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE reportparameter SET parameter_order = 0 WHERE parameter_name LIKE 'Rango de fechas' and parameter_report = (select min(report_id) from report where report_name LIKE 'Propiedades vs Reservas')");
        $this->addSql("UPDATE reportparameter SET parameter_order = 0 WHERE parameter_name LIKE 'Localización' and parameter_report = (select min(report_id) from report where report_name LIKE 'Propiedades vs Reservas')");


    }
}
