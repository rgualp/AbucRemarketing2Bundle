<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160615200322 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        /*Report categories*/
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('cash','accommodationPaymentType')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('bank_deposit','accommodationPaymentType')");

        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'cash'), 'Efectivo')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'bank_deposit'), 'Depósito Bancario')");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("delete nlang from nomenclatorlang nlang join nomenclator nom on nlang.nom_lang_id_nomenclator = nom.nom_id where nom.nom_category LIKE 'accommodationPaymentType'");
        $this->addSql("delete nom from nomenclator nom where nom.nom_category LIKE 'accommodationPaymentType'");

    }
}
