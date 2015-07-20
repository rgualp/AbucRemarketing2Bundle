<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150720230114 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (NULL, 'Reservas-Alojamientos')");
        $this->addSql("set @accommodationParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Reservas-Alojamientos')");

        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@accommodationParentId, 'Solicitudes')");
        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Solicitudes')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Recibidas')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'No disponibles')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Marcadas como disponibles')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Reservadas')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Vencidas')");

        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@accommodationParentId, 'Ingresos')");
        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Ingresos')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Posibles inglesos totales')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Ingresos reales (Casa)')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Ingresos reales (MyCP)')");

        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@accommodationParentId, 'Hospedaje')");
        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Hospedaje')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Huéspedes recibidos')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Noches reservadas')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Habitaciones reservadas')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Ocupación promedio')");

        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@accommodationParentId, 'Comentarios')");
        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Comentarios')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Total')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("set @accommodationParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Reservas-Alojamientos')");

        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Comentarios')");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_parent = @nomParentId");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name LIKE 'Comentarios' AND nom_parent = @accommodationParentId");

        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Hospedaje')");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_parent = @nomParentId");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name LIKE 'Hospedaje' AND nom_parent = @accommodationParentId");

        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Ingresos')");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_parent = @nomParentId");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name LIKE 'Ingresos' AND nom_parent = @accommodationParentId");

        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Solicitudes')");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_parent = @nomParentId");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name LIKE 'Solicitudes' AND nom_parent = @accommodationParentId");

        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name LIKE 'Reservas-Alojamientos'");

    }
}
