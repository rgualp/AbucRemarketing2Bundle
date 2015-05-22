<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150522234538 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE report (report_id INT AUTO_INCREMENT NOT NULL, report_category INT DEFAULT NULL, report_name VARCHAR(255) NOT NULL, report_route_name VARCHAR(255) NOT NULL, report_excel_export_route_name VARCHAR(255) DEFAULT NULL, INDEX IDX_C42F778462C80BC (report_category), PRIMARY KEY(report_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reportParameter (parameter_id INT AUTO_INCREMENT NOT NULL, parameter_type INT DEFAULT NULL, parameter_report INT DEFAULT NULL, parameter_name VARCHAR(255) NOT NULL, INDEX IDX_86C6CFAE23D0542C (parameter_type), INDEX IDX_86C6CFAE20517D75 (parameter_report), PRIMARY KEY(parameter_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778462C80BC FOREIGN KEY (report_category) REFERENCES nomenclator (nom_id)');
        $this->addSql('ALTER TABLE reportParameter ADD CONSTRAINT FK_86C6CFAE23D0542C FOREIGN KEY (parameter_type) REFERENCES nomenclator (nom_id)');
        $this->addSql('ALTER TABLE reportParameter ADD CONSTRAINT FK_86C6CFAE20517D75 FOREIGN KEY (parameter_report) REFERENCES report (report_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reportParameter DROP FOREIGN KEY FK_86C6CFAE20517D75');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE reportParameter');
    }
}
