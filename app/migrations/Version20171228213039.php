<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171228213039 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES
                      ('CORREO MASIVO A PROPIETARIOS FELIZ 2018', 'F2018', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'F2018')");
        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, T.owner, T.email from
                        (select IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
                        IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner
                        from ownership o
                        where o.own_status = 1
                        and ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))) T;");


        $this->addSql(stripslashes("set @contentES = '        
        <p style=\"font-size: 18px\">Que esta Navidad y el Año Nuevo traigan para Ud y familia felicidad y prosperidad.</p>
        <p style=\"font-size: 18px\">¡Qué los éxitos y alegrías nos acompañen en este 2018 donde colaboremos con la energía necesaria para ir tras nuestros sueños y propósitos!!!!</p>
        <p style=\"font-size: 18px\">Feliz 2018 le deseamos los miembros del <b>Equipo de MyCasaParticular</b>.</p>
        <img src=\"http://www.mycasaparticular.com/web/bundles/frontend/img/navidad.jpg\"/>
        '"));

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'MyCasaParticular le desea un feliz 2018', @contentES)");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
