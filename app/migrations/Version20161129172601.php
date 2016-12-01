<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161129172601 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        /*Tipos y estados de notificaciones*/
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('tourist_failure','failureType')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('accommodation_failure','failureType')");

        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values
        ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'tourist_failure'), 'Turista'),
        ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'accommodation_failure'), 'Alojamiento')");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("delete nom from nomenclator nom where nom.nom_category LIKE 'failureType'");
    }
}
