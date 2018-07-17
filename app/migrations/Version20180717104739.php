<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180717104739 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Migrations\AbortMigrationException
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        $this->addSql("UPDATE  ownership  SET  own_facilities_breakfast = 1 , own_facilities_breakfast_include = 1 WHERE  own_mcp_code  IN ('SU001', 'HL026', 'CH1491', 'CH2081', 'CH005', 'CH1277', 'CH1772', 'CH087', 'SS110', 'SS117', 'PR315', 'PR318', 'CH013');");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
