<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170802174916 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES
                      ('CORREO MASIVO A PROPIETARIOS DE RESERVA INMEDIATA', 'REMIND', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'REMIND')");
        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, T.owner, T.email from
                        (select IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
                        IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner
                        from ownership o
                        where o.own_status = 1
                        and o.own_inmediate_booking_2 = 1
                        and ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))) T;");


        $this->addSql(stripslashes("set @contentES = '
        <p>Le recordamos que estamos a las puertas de la temporada alta y su alojamiento se encuentra en la modalidad de reserva inmediata, por ello resulta indispensable que mantenga actualizado su calendario de disponibilidad.</p>
        <p>Usted puede realizar la actualización mediante la aplicación MyCasaRenta, a través del módulo casa <a href=\"http://www.mycasaparticular.com/mycasa\">www.mycasaparticular.com/mycasa</a> o llamando directamente al Servicio de Atención al Cliente (78673574, 78610147).</p>
        <p>Le agradecemos de antemano y nos mantenemos en contacto.</p>
        <p>Saludos cordiales,</p>
        <p>
        <br/>
            <b>Equipo de MyCasaParticular</b>
        </p>'"));

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'Actualice su calendario de disponibilidad', @contentES)");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
