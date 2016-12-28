<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161227232322 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES
                      ('CORREO INFORMANDO A LOS PROPIETARIOS SOBRE EL RANKING', 'NRANK', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'NRANK')");
        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, T.owner, T.email from
                        (select IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
                        IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner
                        from ownership o
                        where o.own_status = 1
                        and ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))) T;");


        $this->addSql("set @contentES = '
        <p><b>MyCasaParticular</b> te tiene preparado algo muy especial para despedir el 2016 porque nuestros anfitriones merecen lo mejor. Estamos aquí para ayudarte a ganar clientes más fácilmente: <b>participa en nuestro ranking y conviértete en una Best Casa</b>.</p>
        <p>Eres bienvenido a seguir nuestros consejos para llegar a las primeras posiciones de tu destino y mejor aún, de todos los alojamientos de nuestro sitio.</p>
        <p>Para conocer más sobre el ranking y tu puntuación, puedes acceder a tu módulo casa: <a href=\"https://www.mycasaparticular.com/mycasa/login\">https://www.mycasaparticular.com/mycasa/login</a>.  En la nueva sección de Estadísticas podrás conocer la posición que ocupas en tu destino, en qué posición estás en el ranking general. Además, podrás ver una vista completa de las recomendaciones para subir en el ranking y cómo convertirte en Best Casa.</p>
        <p>Consejos para convertirte en un Best Casa:</p>
        <p>
            <ul>
                <li>Actualiza tu disponibilidad frecuentemente. Utiliza el módulo Casa, MyCasa Renta o llama a nuestro equipo de Atención al Cliente para ellos actualicen tu calendario.</li>
                <li>Inclúyete en una de nuestras modalidades: Reserva Inmediata y Reserva Rápida, aumentando la posibilidad de que te reserven y a la vez; estando a la vanguardia de los alojamientos en tu destino.</li>
                <li>Trata bien a tus clientes, por cada comentario positivo que tus clientes sean capaces de hacer en nuestra página, sobre tu alojamiento, ganas puntos y un escalón más cercano al premio Best Casa.</li>
                <li>En caso de que te falle un cliente te compensamos con puntos en el ranking que te permitirán ascender y así tendrás más posibilidades de que te reserven.</li>
                <li>Recuerda que la excelencia en tu trabajo es lo que hará que obtengas el premio Best Casa, por tanto, debes evitar los comentarios negativos de los turistas ya que un comentario negativo empaña toda una vida de trabajo duro.</li>
                <li>No canceles nuestras reservas, da prioridad a nuestros clientes y estarás más cerca de ser una Best Casa</li>
            </ul>
        </p>
        <p>El nuevo ranking de MyCasaParticular está diseñado a tu medida, con la actualización de la disponibilidad y siguiendo los consejos para ser un alojamiento Best Casa: </p>
        <p>
            <ul>
                <li>Gana el turista porque podrá reservar tu alojamiento de manera inmediata.</li>
                <li>Gana MyCasaParticular, prestigio y posicionamiento en internet.</li>
                <li>Ganas tú, puntos en el ranking, mayor visibilidad en nuestra página y por ende el aumento de la cantidad de clientes que llegarán a tu casa.</li>
            </ul>
        </p>
        <p>Confiamos en ti, no nos falles y nosotros no te fallaremos. </p>
        <p>
        <br/>
            <span style=\"font-size: 20px\">&#x2665;</span> ¡El equipo de <b>MyCasaParticular</b> te desea éxitos para el nuevo año! <span style=\"font-size: 20px\">&#x1F389;</span>
        </p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'Gana clientes + fácil: conviértete en una Best Casa', @contentES)");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
