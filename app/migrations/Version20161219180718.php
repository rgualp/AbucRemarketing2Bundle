<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161219180718 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ownership_ranking_extra_year (id INT AUTO_INCREMENT NOT NULL, accommodation INT DEFAULT NULL, category INT DEFAULT NULL, year INT NOT NULL, ranking NUMERIC(10, 0) DEFAULT NULL, place INT DEFAULT NULL, destinationPlace INT DEFAULT NULL, currentYearFacturation NUMERIC(10, 0) DEFAULT NULL, totalFacturation NUMERIC(10, 0) DEFAULT NULL, totalAvailableRooms INT DEFAULT NULL, totalNonAvailableRooms INT DEFAULT NULL, totalReservedRooms INT DEFAULT NULL, visits INT DEFAULT NULL, totalAvailableFacturation NUMERIC(10, 0) DEFAULT NULL, totalNonAvailableFacturation NUMERIC(10, 0) DEFAULT NULL, INDEX IDX_DAC7EBAE2D385412 (accommodation), INDEX IDX_DAC7EBAE64C19C1 (category), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ownership_ranking_extra_year ADD CONSTRAINT FK_DAC7EBAE2D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE ownership_ranking_extra_year ADD CONSTRAINT FK_DAC7EBAE64C19C1 FOREIGN KEY (category) REFERENCES nomenclator (nom_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ownership_ranking_extra_year');

    }
}
