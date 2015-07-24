<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150720183130 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("set @nomParent = (SELECT min(nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Alojamientos' AND nom_parent IS NULL)");
        $this->addSql("set @nomSummary = (SELECT min(nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Resúmen' AND nom_parent = @nomParent)");
        $this->addSql("delete from ownershipstat WHERE stat_nomenclator = @nomSummary");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
