<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161202205010 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES
                      ('CORREO PROMOCIÓN OPTIMIZACIÓN A MITAD DE PRECIO', 'OFFER2016', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'OFFER2016')");
        $this->addSql("set @newsletterIdNotInclude = (SELECT min(id) FROM newsletter WHERE code = 'HABFREE')");

        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, T.owner, T.email from
                        (select IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
                        IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner
                        from ownership o
                        where o.own_status = 1
                        and ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))
                        and ((o.own_email_1 NOT IN (SELECT ne.email FROM newsletter_email ne WHERE ne.newsletter = @newsletterIdNotInclude))
                         or (o.own_email_2 NOT IN (SELECT ne2.email FROM newsletter_email ne2 WHERE ne2.newsletter = @newsletterIdNotInclude)))
                        ) T;");


        $this->addSql("set @contentES = '
        <p>Descubre lo que <b>MyCasaParticular</b> ha preparado para ti en vísperas del 2017.</p>
        <p>Todo este mes de diciembre (<b>del 1ro. al 31</b>) puedes optimizar los datos de tu alojamiento en nuestro sitio <b>ahorrándote hasta 10 cuc</b>.</p>
        <p>Te invitamos a actualizar tu información de contacto y los servicios que ofreces. Puedes también agregar nuevas habitaciones de renta con nuestras <b>increíbles tarifas de Navidad</b>.</p>
        <p style=\"margin-left: 20px;\">
            Antes: 1 optimización parcial del perfil de tu alojamiento = 10CUC <br/><br/>
            <b>Ahora te ofrecemos ese servicio por ¡solo 5CUC!</b> <br/><br/>
            Antes: 1 optimización completa del perfil de tu alojamiento = 20CUC <br/><br/>
            <b>Todo el mes de diciembre puedes conseguirlo por apenas 10CUC.</b> <br/><br/>
        </p>
        <p>Disfruta nuestras ofertas <b>a mitad de precio</b>. Agrega nuevas habitaciones para comenzar el 2017 con más capacidad. </p>
        <p>Atrae <span style=\"font-size: 20px\">&#x263A;</span> nuevos clientes, en solo un mes: <b>encárganos las fotos de tu alojamiento</b> por un pequeño costo adicional. Recuerda que una imagen vale más que mil palabras.</p>
        <p>Decídete por las increíbles ofertas que te hemos preparado, <b>disponibles solo para diciembre</b>.</p>
        <p>Llama ya al <b>7867 3574</b> o escríbenos al <a href=\"mailto:casa@mycasaparticular.com\">casa@mycasaparticular.com</a> y solicita una optimización. Aprovecha que <b>ahora es un 50% gratis</b>. ¡Ya queremos ayudarte a actualizar tu perfil!</p>
        <p><span style=\"font-size: 20px\">&#x2764;</span> Feliz Navidad <span style=\"font-size: 20px\">&#x1F385;</span>, </p>
        <p><br/><b>Equipo de MyCasaParticular.com</b></p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'Actualiza el perfil de tu casa desde 5 CUC', @contentES)");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
