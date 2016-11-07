<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161107223035 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES ('CORREO A PROPIETARIOS PIDIENDO RESERVA RAPIDA', 'SOL_RR', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'SOL_RR')");

        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, IF(o.own_homeowner_1 IS NOT NULL, o.own_homeowner_1, o.own_homeowner_2),
                       IF((o.own_email_1 IS NOT NULL AND o.own_email_1 <> ''), o.own_email_1, o.own_email_2)
                       from ownership o
                       WHERE o.own_status = 1
                       AND o.`own_inmediate_booking` = 0 AND o.`own_inmediate_booking_2` = 0
                       AND ((o.own_email_1 IS NOT NULL AND o.own_email_1 <> '') OR (o.own_email_2 IS NOT NULL AND o.own_email_2 <> ''));");


        $this->addSql("set @contentES = '<p>¿Preparado para reservar a lo grande? <b>MyCasaParticular.com</b> te ayuda a multiplicar tus clientes con cero costos y sin moverte de casa a través de la <b>Reserva Rápida</b>.</p>
        <p>A diario, 8 de cada 10 turistas que solicitan alojamiento en nuestra web, eligen este servicio. ¿Quieres estar en el top de las casas más reservadas? Llámanos al <b>7867 3574 o 7861 0147</b> durante esta semana para promocionar tu propiedad con la opción de Reserva Rápida.</p>
        <p>Pensamos en todo, pero, principalmente, pensamos en ti que eres un estupendo anfitrión. Tu alojamiento estará en la preferencia de los viajeros si pueden reservarte antes.</p>
        <p>¿Cómo funciona? Tu propiedad se muestra en <b>MyCasaParticular.com</b> marcada con una señal (un pequeño rayo rojo). El turista solicita una habitación y te llega en instantes un SMS indicando la fecha para conocer tu disponibilidad.</p>
        <p>El turista espera una respuesta de nuestro equipo en aproximadamente una hora. Solo debes comunicarte con nosotros a este mismo número telefónico e indicar el CAS de la reserva (dato que te enviamos en el mensaje de texto) y tu disponibilidad.</p>
        <p>¿Estás preparado para arrasar en Navidad? <b>CONTÁCTANOS AHORA</b> al <b>7867 3574 o 7861 0147</b>.</p>
        <p>Feliz semana, <span style=\"font-size: 20px\">&#x2661;</span></p>
        <p><br/><b>Equipo de MyCasaParticular.com</b></p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'MyCasaParticular.com te ayuda a multiplicar tus reservas', @contentES)");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
