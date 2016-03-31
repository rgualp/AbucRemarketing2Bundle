<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160330172724 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        /*Tipos y estados de notificaciones*/
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('sms_nt','notificationType')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('email_nt','notificationType')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('success_ns','notificationStatus')");
        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('failed_ns','notificationStatus')");

        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'sms_nt'), 'SMS')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'email_nt'), 'Correo')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'success_ns'), 'Envío exitoso')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'failed_ns'), 'Envío fallido')");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("delete nlang from nomenclatorlang nlang join nomenclator nom on nlang.nom_lang_id_nomenclator = nom.nom_id where nom.nom_category LIKE 'notificationStatus' or nom.nom_category LIKE 'notificationType'");
        $this->addSql("delete nom from nomenclator nom where nom.nom_category LIKE 'notificationStatus' or nom.nom_category LIKE 'notificationType'");

    }
}
