<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Setting values to own_automatic_mcp_code column
 */
class Version20150513183948 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("update ownership o set o.own_automatic_mcp_code = SUBSTR(o.own_mcp_code,3) where SUBSTR(own_mcp_code,3) REGEXP '^-?[0-9]+$';");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("update ownership set own_automatic_mcp_code = NULL");

    }
}
