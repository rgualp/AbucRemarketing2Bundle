<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150618175040 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ownershipstat (stat_id INT AUTO_INCREMENT NOT NULL, stat_municipality INT DEFAULT NULL, stat_nomenclator INT DEFAULT NULL, stat_value VARCHAR(255) NOT NULL, INDEX IDX_707D1D646E4A7B82 (stat_municipality), INDEX IDX_707D1D64210B833C (stat_nomenclator), PRIMARY KEY(stat_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ownershipstat ADD CONSTRAINT FK_707D1D646E4A7B82 FOREIGN KEY (stat_municipality) REFERENCES municipality (mun_id)');
        $this->addSql('ALTER TABLE ownershipstat ADD CONSTRAINT FK_707D1D64210B833C FOREIGN KEY (stat_nomenclator) REFERENCES nomenclatorstat (nom_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ownershipstat');
    }
}
