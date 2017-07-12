<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170710175428 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        /*Tipos de pago a propietarios*/
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('completePayment','acommodationPayment')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('cancelPayment','acommodationPayment')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('clientFailurePayment','acommodationPayment')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('completePaymentAgency','acommodationPayment')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('cancelPaymentAgency','acommodationPayment')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('clientFailurePaymentAgency','acommodationPayment')");

        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'completePayment'), 'Pago Completo')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'cancelPayment'), 'Cancelación')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'clientFailurePayment'), 'Fallo de Cliente')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'completePaymentAgency'), 'Pago Completo - Agencia')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'cancelPaymentAgency'), 'Cancelación - Agencia')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'clientFailurePaymentAgency'), 'Fallo de Cliente - Agencia')");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
