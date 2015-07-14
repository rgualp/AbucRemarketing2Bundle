<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150714223119 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE offerlog (nom_id INT AUTO_INCREMENT NOT NULL, log_offer_reservation INT DEFAULT NULL, log_from_reservation INT DEFAULT NULL, log_reason INT DEFAULT NULL, log_date DATETIME NOT NULL, INDEX IDX_BAB8D91BF5BBDD2 (log_offer_reservation), INDEX IDX_BAB8D91B61B86484 (log_from_reservation), INDEX IDX_BAB8D91B2E42DBAE (log_reason), PRIMARY KEY(nom_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE offerlog ADD CONSTRAINT FK_BAB8D91BF5BBDD2 FOREIGN KEY (log_offer_reservation) REFERENCES generalreservation (gen_res_id)');
        $this->addSql('ALTER TABLE offerlog ADD CONSTRAINT FK_BAB8D91B61B86484 FOREIGN KEY (log_from_reservation) REFERENCES generalreservation (gen_res_id)');
        $this->addSql('ALTER TABLE offerlog ADD CONSTRAINT FK_BAB8D91B2E42DBAE FOREIGN KEY (log_reason) REFERENCES nomenclatorstat (nom_id)');
        /*$this->addSql('ALTER TABLE nomenclatorstat DROP FOREIGN KEY FK_6C46155BD41FF18');
        $this->addSql('DROP INDEX idx_6c46155bd41ff18 ON nomenclatorstat');
        $this->addSql('CREATE INDEX IDX_A6F6CE6BBD41FF18 ON nomenclatorstat (nom_parent)');
        $this->addSql('ALTER TABLE nomenclatorstat ADD CONSTRAINT FK_6C46155BD41FF18 FOREIGN KEY (nom_parent) REFERENCES nomenclatorstat (nom_id)');
        $this->addSql('ALTER TABLE ownershipstat DROP FOREIGN KEY FK_707D1D64210B833C');
        $this->addSql('ALTER TABLE ownershipstat DROP FOREIGN KEY FK_707D1D646E4A7B82');
        $this->addSql('DROP INDEX idx_707d1d646e4a7b82 ON ownershipstat');
        $this->addSql('CREATE INDEX IDX_D04FB25A6E4A7B82 ON ownershipstat (stat_municipality)');
        $this->addSql('DROP INDEX idx_707d1d64210b833c ON ownershipstat');
        $this->addSql('CREATE INDEX IDX_D04FB25A210B833C ON ownershipstat (stat_nomenclator)');
        $this->addSql('ALTER TABLE ownershipstat ADD CONSTRAINT FK_707D1D64210B833C FOREIGN KEY (stat_nomenclator) REFERENCES nomenclatorstat (nom_id)');
        $this->addSql('ALTER TABLE ownershipstat ADD CONSTRAINT FK_707D1D646E4A7B82 FOREIGN KEY (stat_municipality) REFERENCES municipality (mun_id)');
        $this->addSql('ALTER TABLE reportparameter DROP FOREIGN KEY FK_86C6CFAE20517D75');
        $this->addSql('ALTER TABLE reportparameter DROP FOREIGN KEY FK_86C6CFAE23D0542C');
        $this->addSql('DROP INDEX idx_86c6cfae23d0542c ON reportparameter');
        $this->addSql('CREATE INDEX IDX_4374D0D23D0542C ON reportparameter (parameter_type)');
        $this->addSql('DROP INDEX idx_86c6cfae20517d75 ON reportparameter');
        $this->addSql('CREATE INDEX IDX_4374D0D20517D75 ON reportparameter (parameter_report)');
        $this->addSql('ALTER TABLE reportparameter ADD CONSTRAINT FK_86C6CFAE20517D75 FOREIGN KEY (parameter_report) REFERENCES report (report_id)');
        $this->addSql('ALTER TABLE reportparameter ADD CONSTRAINT FK_86C6CFAE23D0542C FOREIGN KEY (parameter_type) REFERENCES nomenclator (nom_id)');
    */
        }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE offerlog');
        /*$this->addSql('ALTER TABLE nomenclatorstat DROP FOREIGN KEY FK_A6F6CE6BBD41FF18');
        $this->addSql('DROP INDEX idx_a6f6ce6bbd41ff18 ON nomenclatorstat');
        $this->addSql('CREATE INDEX IDX_6C46155BD41FF18 ON nomenclatorstat (nom_parent)');
        $this->addSql('ALTER TABLE nomenclatorstat ADD CONSTRAINT FK_A6F6CE6BBD41FF18 FOREIGN KEY (nom_parent) REFERENCES nomenclatorstat (nom_id)');
        $this->addSql('ALTER TABLE ownershipstat DROP FOREIGN KEY FK_D04FB25A6E4A7B82');
        $this->addSql('ALTER TABLE ownershipstat DROP FOREIGN KEY FK_D04FB25A210B833C');
        $this->addSql('DROP INDEX idx_d04fb25a6e4a7b82 ON ownershipstat');
        $this->addSql('CREATE INDEX IDX_707D1D646E4A7B82 ON ownershipstat (stat_municipality)');
        $this->addSql('DROP INDEX idx_d04fb25a210b833c ON ownershipstat');
        $this->addSql('CREATE INDEX IDX_707D1D64210B833C ON ownershipstat (stat_nomenclator)');
        $this->addSql('ALTER TABLE ownershipstat ADD CONSTRAINT FK_D04FB25A6E4A7B82 FOREIGN KEY (stat_municipality) REFERENCES municipality (mun_id)');
        $this->addSql('ALTER TABLE ownershipstat ADD CONSTRAINT FK_D04FB25A210B833C FOREIGN KEY (stat_nomenclator) REFERENCES nomenclatorstat (nom_id)');
        $this->addSql('ALTER TABLE reportparameter DROP FOREIGN KEY FK_4374D0D23D0542C');
        $this->addSql('ALTER TABLE reportparameter DROP FOREIGN KEY FK_4374D0D20517D75');
        $this->addSql('DROP INDEX idx_4374d0d23d0542c ON reportparameter');
        $this->addSql('CREATE INDEX IDX_86C6CFAE23D0542C ON reportparameter (parameter_type)');
        $this->addSql('DROP INDEX idx_4374d0d20517d75 ON reportparameter');
        $this->addSql('CREATE INDEX IDX_86C6CFAE20517D75 ON reportparameter (parameter_report)');
        $this->addSql('ALTER TABLE reportparameter ADD CONSTRAINT FK_4374D0D23D0542C FOREIGN KEY (parameter_type) REFERENCES nomenclator (nom_id)');
        $this->addSql('ALTER TABLE reportparameter ADD CONSTRAINT FK_4374D0D20517D75 FOREIGN KEY (parameter_report) REFERENCES report (report_id)');
    */
        }
}
