<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Generating codes with new mechanism
 */
class Version20150513185327 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("update ownership o set o.own_mcp_code_generated = concat((select min(p.prov_own_code) from province p where p.prov_id = o.own_address_province), lpad(o.own_automatic_mcp_code, 3, 0)) where length(o.own_automatic_mcp_code) < 3;");
        $this->addSql("update ownership o set o.own_mcp_code_generated = concat((select min(p.prov_own_code) from province p where p.prov_id = o.own_address_province), o.own_automatic_mcp_code) where length(o.own_automatic_mcp_code) >= 3;");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("update ownership set own_mcp_code_generated = NULL");
    }
}
