<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161212172303 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        /*Accommodation modalities*/
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('rrModality','accommodationModality')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('riModality','accommodationModality')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('normalModality','accommodationModality')");

        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values
        ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'rrModality'), 'Reserva Rápida'),
        ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'riModality'), 'Reserva Inmediata'),
        ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'normalModality'), 'Solicitud de Disponibilidad')");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("delete nom from nomenclator nom where nom.nom_category LIKE 'accommodationModality'");
    }
}
