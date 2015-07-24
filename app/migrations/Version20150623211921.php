<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150623211921 extends AbstractMigration
{
    public function up(Schema $schema)
    {   $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("set @nomParentId = (SELECT min(nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Resúmen')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Total')");

    }

    public function down(Schema $schema)
    {  $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
       $this->addSql("set @nomParentId = (SELECT min(nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Resúmen')");
       $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name = 'Total' AND nom_parent= @nomParentId");

    }
}
