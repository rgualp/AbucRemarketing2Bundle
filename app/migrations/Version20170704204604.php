<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170704204604 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES
                      ('CORREO A PROPIETARIOS DESDE CONTABILIDAD', 'CONTAB', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'CONTAB')");
        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                        select DISTINCT @newsletterId, @langES, T.owner, T.email from
                        (select IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
                        IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner
                        from ownership o
                        where o.own_status = 1
                        and o.own_mcp_code LIKE 'CH%'
                        and (SELECT count(*) from generalreservation gres where gres.`gen_res_own_id` = o.own_id and gres.gen_res_status = 2) > 0
                        and ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))) T;");


        $this->addSql("set @contentES = '
        <p>Como parte de la permanente renovación y mejoramiento de nuestro sistema de reservas, está previsto que en el transcurso de 2 a 3 meses comencemos a realizar los pagos completos a los propietarios por vía bancaria directamente a sus tarjetas magnéticas en CUC o a cuentas en bancos en el extranjero, según prefieran.</p>
        <p>Este cambio moderniza el funcionamiento del sistema, lo hace más cómodo para los turistas al no tener que manejar dinero en efectivo y sobre todo, garantiza la seguridad del pago a los propietarios en plazos mucho más cortos con respecto a otras plataformas que gestionan alojamientos en Cuba.</p>
        <p>Debido a lo anteriormente descrito, les solicitamos por favor nos envíen la información del número de tarjeta magnética en CUC de que disponen (puede ser lo mismo de BANDEC que de Banco Metropolitano, pero solamente en CUC), o todos los datos de la cuenta en el exterior (beneficiario, banco, número de cuenta, etc.) en el formato siguiente:</p>
        <p>
            <ul>
                <li><b>Código de la casa en MyCP</b>. Ejemplo: CHXXX</li>
                <li><b>Número de tarjeta</b>. Ejemplo: XXXX XXXX XXXX XXXX</li>
                <li><b>Banco emisor</b>. Ejemplo: BANDEC o B. Metropolitano</li>
            </ul>
        </p>
        <p>Les damos las gracias anticipadas por vuestra colaboración. Aprovechamos para expresarles el testimonio de nuestra más alta consideración y estima.</p>
        <p>Reciban cordiales saludos;</p>
        <p>
        <br/>
            <b>Equipo de MyCasaParticular</b>
        </p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'Requisitos necesarios para recibir el pago completo', @contentES)");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
