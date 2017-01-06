<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161219211900 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("set @nomParentId = (SELECT min(nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Resúmen')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Reserva Rápida')");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("set @nomParentId = (SELECT min(nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Resúmen')");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_parent = @nomParentId AND nom_name = 'Reserva Rápida'");

    }
}
