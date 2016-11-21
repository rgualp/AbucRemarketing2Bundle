<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161121221254 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES ('CORREO SOLICITANDO OFERTAS DE PROMOCIÓN', 'PROMO2016', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'PROMO2016')");
        $this->addSql("set @newsletterIdNotInclude = (SELECT min(id) FROM newsletter WHERE code = 'HABFREE')");

        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, T.owner, T.email from
                        (select IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
                        IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner
                        from ownership o
                        where o.own_status = 1
                        and ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))) T
                        WHERE T.email NOT IN (SELECT ne.email FROM newsletter_email ne WHERE ne.newsletter = @newsletterIdNotInclude);");


        $this->addSql("set @contentES = '
        <p>Un nuevo año se acerca y la oportunidad de subir nuestras ventas está a la vuelta de la esquina. Es un hecho que los huéspedes aman viajar a Cuba para las fiestas de Navidad y fin de año.</p>
        <p>Un buen anfitrión debe estar preparado para recibir a los clientes con opciones de lujo por estas fechas. En <b>MyCasaParticular</b> todos somos una gran familia, así que te ayudamos a promocionar tus increíbles ofertas <b>¡gratis!</b></p>
        <p>Si ofreces una cena de navidad, un descuento en tus precios, un servicio extra…lo publicitamos en las redes sociales de <b>MyCasaParticular</b>. Te garantizamos que tus propuestas de navidad y fin de año serán muy atractivas para los viajeros.</p>
        <p>Desde las redes sociales, cualquier turista podrá reservar tu alojamiento y disfrutar de las opciones especiales que estás preparando. Solicita nuestra promoción gratuita a través del correo electrónico <a href=\"mailto:casa@mycasaparticular.com\">casa@mycasaparticular.com</a></p>
        <p>Envíanos tu oferta con el precio, todos los detalles posibles y el nombre o código de tu casa en nuestro sitio.</p>
        <p>Estamos recibiendo las propuestas hasta el 30 de noviembre de 2016 y se publicarán de manera alternativa en nuestra página de Facebook <a href=\"https://www.facebook.com/MyCasaParticular\">https://www.facebook.com/MyCasaParticular/</a> durante todo el mes de diciembre.</p>
        <p>Gana clientes con ofertas especiales por fin de año…echa a volar la imaginación y multiplica tus reservas. Te promocionamos con <span style=\"font-size: 20px\">&#x2661;</span></p>
        <p><br/><b>Equipo de MyCasaParticular.com</b></p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'MyCasaParticular promociona tus ofertas de fin de año', @contentES)");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
