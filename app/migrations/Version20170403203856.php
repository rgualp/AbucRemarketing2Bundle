<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170403203856 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES
                      ('Correo propietarios para agregar habitaciones', 'ADDROOM', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'ADDROOM')");
        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, T.owner, T.email from
                        (select IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
                        IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner
                        from ownership o
                        where o.own_status = 1
                        and ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))) T;");


        $this->addSql("set @contentES = '
        <p>El equipo de <b>MyCasaParticular</b> llega complaciendo peticiones y trabajando para alcanzar nuevos índices de excelencia. Esta vez, enfocados en optimizar tu tiempo, desarrollamos una forma MÁS FÁCIL y MÁS RAPIDA de modificar la cantidad de habitaciones activas en tu alojamiento y completamente gratis.</p>
        <p>
            A partir de ahora puede entrar directamente al apartado <b>HABITACIONES</b> y seleccionar el botón <b>Adicionar</b>  al final de la lista para agregar cada nueva habitación con todas sus características (cantidad de camas, precio por temporada, las facilidades que brindas y los servicios que prestas).
        </p>

        <p>
            Una vez que completes todos los datos de cada nueva habitación debes <b>GUARDAR</b> los cambios para que se hagan efectivos y públicos en el sitio web.
        </p>
        <p>¡Genial!, ¿verdad? Queremos que sepas que trabajamos pensando en ti y que tus comentarios y recomendaciones nos hacen cada día mejor.</p>
        <p>&#x263B;&#x263A;Te deseamos una feliz jornada llenita de reservas&#x263A;&#x263B;</p>
        <p>
        <br/>
            <b>&#x2665; Equipo de MyCasaParticular &#x2665;</b>
        </p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'Nuevas habitaciones. Más fácil y mas rápido', @contentES)");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
