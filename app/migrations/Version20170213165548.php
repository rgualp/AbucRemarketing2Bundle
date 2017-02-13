<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170213165548 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES
                      ('CORREO MASIVO A PROPIETARIOS POR EL 14 DE FEBRERO', '14FEB', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = '14FEB')");
        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, T.owner, T.email from
                        (select IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
                        IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner
                        from ownership o
                        where o.own_status = 1
                        and ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))) T;");


        $this->addSql("set @contentES = '
        <p>¿Listo para convertirte en un propietario &#x1f31f;&#x1f31f;&#x1f31f;&#x1f31f;&#x1f31f;? Entonces…allá vamos!!!</p>
        <p><b>MyCasaParticular</b> es una gran familia que vela por el bienestar de todos sus miembros y estamos muy felices de que formes parte de ella. En este 14 de Febrero queremos aprovechar la ocasión para agradecerte por elegirnos y recordarte que nuestro éxito depende de ti, no nos falles.</p>
        <p>Es una realidad que los viajeros adoran las fechas festivas para viajar a la Isla. Es por eso que como anfitrión debes estar listo para brindar a tus huéspedes un servicio de calidad suprema. ¿Cómo? &#x1f59d; Con nuestros consejos.</p>
        <p>Siempre es buena oportunidad para incrementar las ventas y actualizar tu disponibilidad con frecuencia es un factor sustantivo para ello. Además, recuerda que miles de personas de distintas regiones del mundo deciden alojarse con nosotros cada año por el trato cálido y hospitalario que ofrecemos. Pero tener un toque distintivo podría ser la clave del éxito en este Día del Amor y la Amistad. No es complicado!!! El secreto está en que descubran en tu negocio algo único. Puede ser tú café, tu manera de hacer la limpieza o ese bombón que a veces pones en la recámara. ¡Sorpréndelos!</p>
        <p><b>MyCasaParticular</b> te felicita de todo &#x2665; y te desea un ¡Feliz San Valentín! Esperamos que lo celebres en compañía de esa persona que hace que cada día de tu vida sea especial. Así como tú lo eres para nosotros.</p>
        <p>
        <br/>
            <b>Equipo de MyCasaParticular</b>
        </p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, '¡Feliz día del amor!', @contentES)");



    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
