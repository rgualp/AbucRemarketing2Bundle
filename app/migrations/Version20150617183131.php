<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150617183131 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (NULL, 'Alojamientos')");
        $this->addSql("set @accommodationParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Alojamientos')");

        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@accommodationParentId, 'Estados')");

        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Estados')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Activo')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Inactivo')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'En proceso')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Eliminar')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'En proceso (por lotes)')");

        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@accommodationParentId, 'Tipos')");
        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Tipos')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Casa particular')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Propiedad completa')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Apartamento')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Penthouse')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Villa con piscina')");

        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@accommodationParentId, 'Categorías')");
        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Categorías')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Económica')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Rango medio')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Premium')");

        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@accommodationParentId, 'Idiomas')");
        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Idiomas')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Inglés')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Francés')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Alemán')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Italiano')");

        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@accommodationParentId, 'Resúmen')");
        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Resúmen')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Casa Selección')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, 'Reserva Inmediata')");

        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@accommodationParentId, 'Total de Habitaciones')");
        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Total de Habitaciones')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, '1')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, '2')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, '3')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, '4')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, '5')");
        $this->addSql("INSERT INTO nomenclatorstat (nom_parent, nom_name) VALUES (@nomParentId, '+5')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("set @accommodationParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Alojamientos')");

        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Estados')");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_parent = @nomParentId");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name LIKE 'Estados' AND nom_parent = @accommodationParentId");

        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Tipos')");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_parent = @nomParentId");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name LIKE 'Tipos' AND nom_parent = @accommodationParentId");

        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Categorías')");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_parent = @nomParentId");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name LIKE 'Categorías' AND nom_parent = @accommodationParentId");

        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Idiomas')");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_parent = @nomParentId");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name LIKE 'Idiomas' AND nom_parent = @accommodationParentId");

        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Resúmen')");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_parent = @nomParentId");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name LIKE 'Resúmen' AND nom_parent = @accommodationParentId");

        $this->addSql("set @nomParentId = (SELECT (nom_id) FROM nomenclatorstat WHERE nom_name LIKE 'Total de Habitaciones')");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_parent = @nomParentId");
        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name LIKE 'Total de Habitaciones' AND nom_parent = @accommodationParentId");

        $this->addSql("DELETE FROM nomenclatorstat WHERE nom_name LIKE 'Alojamientos'");
    }
}
