<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161116225935 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES ('CORREO PROMOCIÓN DE ACTUALIZACIÓN DE HABITACIONES FREE', 'HABFREE', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'HABFREE')");

        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, T.owner, T.email from
                        (select o.own_id, o.own_mcp_code, IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
                        IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner, o.own_name, o.own_ranking,
                        (SELECT COUNT(*) FROM ownershipreservation owr JOIN generalreservation gres on owr.own_res_gen_res_id = gres.gen_res_id
                        JOIN booking b on b.booking_id = owr.own_res_reservation_booking WHERE owr.own_res_status = 5 AND gres.gen_res_own_id = o.own_id) as totalReservations
                        from ownership o
                        where o.own_status = 1
                        AND ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))) T
                        order by T.totalReservations DESC, T.own_ranking DESC, T.own_id ASC
                        LIMIT 100;");


        $this->addSql("set @contentES = '
        <p><b>MyCasaParticular.com</b> valora a sus anfitriones de élite… y mucho. Sabemos que durante todo este año has ofrecido un servicio de excelencia, hospedando a clientes de todas las latitudes.</p>
        <p>El buen trato es la clave del triunfo en los negocios de hostelería como el nuestro. La gran familia que es <b>MyCasaParticular</b> te agradece el esfuerzo, la colaboración y el entusiasmo con que gestionas tu alojamiento junto a nosotros.</p>
        <p>Por eso hemos querido premiarte en esta oportunidad subiendo <b>gratis</b> a nuestro sitio tu(s) nueva(s) habitación(es). Si rentas algún espacio que no se muestra en tu perfil de <b>MyCasaParticular</b> este es el momento de hacerlo ¡sin costo alguno!</p>
        <p>Llámanos al <b>7 867 3574</b> para conocer todos los detalles.  Comenzaremos a recibir sus solicitudes para actualizar las nuevas habitaciones a partir del <b>1ro. de diciembre y hasta el 31 de diciembre</b> de 2016. </p>
        <p>Son varios años juntos y estamos seguros de seguir creciendo por este camino. Ser cada día más rentables y exitosos es una meta para nuestro equipo y un compromiso con los anfitriones especiales como tú.</p>
        <p>Actualiza tus nuevas habitaciones gratis todo el mes de diciembre. ¡Escríbenos!</p>
        <p>Porque eres un anfitrión <span style=\"font-size: 20px\">&#x2606;</span></p>
        <p><br/><b>Equipo de MyCasaParticular.com</b></p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'PROMO MyCasaParticular: ¡Subimos tus nuevas habitaciones GRATIS!!!', @contentES)");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
