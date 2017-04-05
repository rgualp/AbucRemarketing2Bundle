<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170403173750 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES
                      ('CORREO A PROPIETARIOS POLITICA DE CANCELACION', 'PCANCEL', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'PCANCEL')");
        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, T.owner, T.email from
                        (select IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
                        IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner
                        from ownership o
                        where o.own_status = 1
                        and ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))) T;");


        $this->addSql("set @contentES = '
        <p>El equipo de <b>MyCasaParticular</b> actualiza sus <a href=\"https://www.mycasaparticular.com/es/terminos-legales/\">Términos y Condiciones</a> para minimizar los riesgos y ofrecerte la mayor garantía como propietario.</p>
        <p>A partir del <b>5 de abril de 2017</b>, se hará vigente que si algún cliente te cancela una reserva confirmada, obtendrás en compensación un puntaje positivo en el ranking de nuestra plataforma, lo que te ayudará a recibir mayor número de solicitudes en el futuro. Además, hasta 24 horas después de la cancelación, podrás solicitar un determinado pago por indemnización, por ejemplo:</p>
        <p>
            <ul>
                <li>Si un turista cancela la reserva de 4 o 5 noches, con entre 3 y 7 días de antelación a la fecha reservada, cobrarás el 50% del precio de tu habitación por 1 noche.</li>
                <li>Si un turista cancela la reserva de 6 o más noches, con entre 3 y 7 días antes de la fecha reservada, cobrarás el 100% del precio de tu habitación por 1 noche.</li>
                <li>Si un turista cancela o falla la reserva de 1 o 2 noches, con menos de 2 días de antelación, cobrarás el 50% del precio de tu habitación por 1 noche.</li>
                <li>Si un turista cancela o falla la reserva de 3 o más noches, con menos de 2 días de antelación, cobrarás el 100% del precio de tu habitación por 1 noche.</li>
                <li>Si una agencia cancela o falla una reservación con 7 o menos días de antelación a la reserva, cobrarás el 100% del precio de tu habitación por 1 noche.</li>
                <li>Ahora cuidado, porque si eres tú quien cancela una reserva confirmada, serás penalizado en el ranking de nuestra plataforma.</li>
            </ul>
        </p>
        <p>
            Para más información podrás consultar en los <a href=\"https://www.mycasaparticular.com/es/terminos-legales/\">Términos y Condiciones</a> del sitio los apartados de la <b>Política de Precios</b> y la <b>Política de Cancelaciones y Reembolsos</b>, donde se explican a detalle tus derechos y deberes.  Además, recuerda que nuestro servicio de atención al cliente, te brindará siempre toda la ayuda que necesites…porque la información es Poder.
        </p>
        <p>Te esperamos!!</p>
        <p>Feliz día, &#x263B;&#x263A;</p>
        <p>
        <br/>
            <b>&#x2665; Equipo de MyCasaParticular &#x2665;</b>
        </p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'MyCasaParticular: Actualizamos nuestros Términos y Condiciones', @contentES)");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
