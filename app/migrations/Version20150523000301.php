<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150523000301 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        /*Report categories*/
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('general','reportCategory')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('revision','reportCategory')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('reservations','reportCategory')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('clients','reportCategory')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('payments','reportCategory')");

        $this->addSql("insert into nomenclatorLang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'general'), 'Generales')");
        $this->addSql("insert into nomenclatorLang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'revision'), 'Revision')");
        $this->addSql("insert into nomenclatorLang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'reservations'), 'Reservaciones')");
        $this->addSql("insert into nomenclatorLang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'clients'), 'Clientes')");
        $this->addSql("insert into nomenclatorLang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'payments'), 'Pagos')");

        /*Parameter types*/
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('date','parameterType')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('dateRangeFrom','parameterType')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('dateRangeTo','parameterType')");

        $this->addSql("insert into nomenclatorLang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'date'), 'Fecha')");
        $this->addSql("insert into nomenclatorLang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'dateRangeFrom'), 'Rango de Fecha (Desde)')");
        $this->addSql("insert into nomenclatorLang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'dateRangeTo'), 'Rango de Fecha (Hasta)')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("delete nlang from nomenclatorlang nlang join nomenclator nom on nlang.nom_lang_id_nomenclator = nom.nom_id where nom.nom_category LIKE 'parameterType' or nom.nom_category LIKE 'reportCategory'");
        $this->addSql("delete nom from nomenclator nom where nom.nom_category LIKE 'parameterType' or nom.nom_category LIKE 'reportCategory'");
    }
}
