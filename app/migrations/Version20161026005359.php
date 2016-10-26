<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161026005359 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE ownership SET own_inmediate_booking_2 = 0;');

        $this->addSql('UPDATE ownership
                    SET own_inmediate_booking_2 = 1
                    WHERE own_mcp_code IN ("CH1486", "CH005", "CH1388", "CH1368", "CH1366", "CH1277", "CH1048",
                    "CH1139", "CH087", "CH1416", "CH671", "CH1327", "CH1343", "CH1502", "CH1321", "CH1341", "CH1347",
                    "CH1495", "CH1418", "CH1420");');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE ownership
                    SET own_inmediate_booking_2 = 0
                    WHERE own_mcp_code IN ("CH1486", "CH005", "CH1388", "CH1368", "CH1366", "CH1277", "CH1048",
                    "CH1139", "CH087", "CH1416", "CH671", "CH1327", "CH1343", "CH1502", "CH1321", "CH1341", "CH1347",
                    "CH1495", "CH1418", "CH1420");');

    }
}
