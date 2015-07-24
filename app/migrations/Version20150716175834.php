<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150716175834 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (NULL, 'Oferta')");
        $this->addSql("set @nomParentId = (SELECT min(nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Oferta')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Modificación de fechas')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Ofrecer nuevo alojamiento')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("set @nomParentId = (SELECT min(nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Oferta')");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name = 'Modificación de fechas' AND nom_parent= @nomParentId");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name = 'Ofrecer nuevo alojamiento' AND nom_parent= @nomParentId");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name = 'Oferta' AND nom_parent IS NULL");
    }
}
