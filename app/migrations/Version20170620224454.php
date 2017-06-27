<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170620224454 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES
                      ('CORREO MASIVO A PROPIETARIOS POR PROMOCION DE PETIT FUTE', 'PETIT', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'PETIT')");
        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, T.owner, T.email from
                        (select IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
                        IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner
                        from ownership o
                        where o.own_status = 1
                        and ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))) T;");


        $this->addSql("set @contentES = '
        <p>Es real que el turismo internacional en Cuba aumenta, pero también aumentan la cantidad de casas que hoy deciden apostar por el negocio de alquiler en la isla.</p>
        <p>Es real que se han flexibilizado barreras internacionales que antes estaban cerradas, muchas agencias de viaje y turistas han apostado por Cuba como destino, enigmático y a la vez exótico y tropical.</p>
        <p>Ante estas realidades, <b>MyCasaParticular</b> se une a Petit Futé y te traen una propuesta ¿Qué es Petit Futé? una guía francesa que llega a más de un millón de franco hablantes y que se distribuye en formato duro o guía impresa, en página web o página de internet como normalmente se conoce y por supuesto, su distribución móvil para los dispositivos con sistemas operativos, Android, IOS y Windows Phone.</p>
        <p>¿La propuesta? es una nueva oferta de promoción para su negocio, es la posibilidad de conectar con millones de viajeros, gracias a Petit Futé.</p>
        <p>El hecho de que tu negocio se promocione en esa guía lo convierte, junto a otros pocos afortunados en tu destino, en ALOJAMIENTO SELECTO,  convierte tu negocio en la diana perfecta donde irán a parar nuevos visitantes y por supuesto la ventaja competitiva de nuevas oportunidades.</p>
        <p>La oferta estará disponible hasta el 20 de julio ¡llame ya a <b>MyCasaParticular</b>! e infórmate de precios y ventajas para tu negocio, recuerda la diferencia entre “Costo y Valor”, el costo por la publicidad de tu negocio, es alto, pero el valor de que tu negocio sea referencia en una guía como Petit Futé es <b>INMENSO</b>.</p>
        <p>
        <br/>
            <b>Equipo de MyCasaParticular</b>
        </p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, '¿Cómo mantener tu alojamiento lleno todo el año?', @contentES)");



    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
