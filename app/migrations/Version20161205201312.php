<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161205201312 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        /*Rankings categorias*/
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('gold_ranking','rankingCategory')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('silver_ranking','rankingCategory')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('bronze_ranking','rankingCategory')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('amateur_ranking','rankingCategory')");

        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values
        ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'gold_ranking'), 'Oro'),
        ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'silver_ranking'), 'Plata'),
        ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'bronze_ranking'), 'Bronce'),
        ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'amateur_ranking'), 'Amateur')");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("delete nom from nomenclator nom where nom.nom_category LIKE 'rankingCategory'");
    }
}
