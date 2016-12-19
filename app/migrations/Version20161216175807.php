<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161216175807 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ownership_ranking_extra ADD currentMonthFacturation NUMERIC(10, 0) DEFAULT NULL, ADD totalFacturation NUMERIC(10, 0) DEFAULT NULL, ADD totalAvailableRooms INT DEFAULT NULL, ADD totalNonAvailableRooms INT DEFAULT NULL, ADD totalReservedRooms INT DEFAULT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ownership_ranking_extra DROP currentMonthFacturation, DROP totalFacturation, DROP totalAvailableRooms, DROP totalNonAvailableRooms, DROP totalReservedRooms');

    }
}
