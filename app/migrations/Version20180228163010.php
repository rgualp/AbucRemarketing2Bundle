<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180228163010 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
        UPDATE ownership_ranking_extra ranking JOIN ownership own
on ranking.accommodation = own.own_id
SET ranking.confidence = 5,ranking.fad=5
where own.own_mcp_code='CH2094' or
own.own_mcp_code='CH2101' or
own.own_mcp_code='CH2103' or
own.own_mcp_code='CH2104' or
own.own_mcp_code='CH2106' or
own.own_mcp_code='CH2110' or
own.own_mcp_code='CH2113' or
own.own_mcp_code='CH2116' or
own.own_mcp_code='CH2117' or
own.own_mcp_code='CH2120' 
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
