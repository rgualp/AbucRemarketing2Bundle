<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170109193328 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES
                      ('CORREO INFORMANDO A LOS PROPIETARIOS SOBRE LA SALIDA DE MYCASARENTA', 'APPRENTA', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'APPRENTA')");
        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, T.owner, T.email from
                        (select IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
                        IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner
                        from ownership o
                        where o.own_status = 1
                        and ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))) T;");


        $this->addSql("set @contentES = '
        <p><b>MyCasaParticular</b> te tiene preparado la versión 2.0 de MycasaRenta, una aplicación que le permitirá a tu negocio crecer de manera exponencial en este 2017, porque tú te mereces lo mejor.</p>
        <p>Estamos aquí para ayudar a que tu <b>CASA</b> esté <b>LLENA</b> de clientes todo el año, estamos aquí para facilitarte el trabajo de arrendador, en fin, estamos aquí para ayudarte a <b>GANAR</b>.</p>
        <p><b>5 maneras de facilitarte el trabajo con MycasaRenta.</b></p>
        <p><b>Con MycasaRenta puedes:</b></p>
        <p>
            <ol>
                <li>Actualizar la disponibilidad en la comodidad de tu casa sin internet y hacérnosla llegar en el momento que desees por SMS (Sin necesidad de conexión) o por WIFI.</li>
                <li>Recibir notificaciones sobre las confirmaciones de los clientes, los recordatorios de llegada del turista, las reservas y los consejos que MyCasaParticular les brinda para lograr que su alojamiento destaque en nuestro sitio.</li>
                <li>Aumentar y disminuir el precio de las habitaciones, sin necesidad de comunicarte con nosotros, tu habitación tiene un precio en cada temporada y podrás cambiarlo a tu conveniencia.</li>
                <li>MycasaRenta 2.0 está pensada para convertirse en tu agenda electrónica llevando el control de tus reservas y permitiéndote almacenar, el nombre y correo de los clientes que han confirmado la llegada a tu casa, la fecha de arribo a tu alojamiento y la comunicación con ellos de manera inmediata.</li>
                <li>Restaurar todos los datos que tienes en MyCasaParticular, en caso de que creas que has cometido un error (Mientras cambias la disponibilidad o cambias los precios de las habitaciones) y no recuerdas cual fue, puedes siempre, <b>descargar todo</b> y se restablecerá la información que está almacenada en MyCasaParticular, dándote la posibilidad de comenzar de nuevo.</li>
            </ol>
        </p>
        <p>&#x1f441;J&#x1f441;: Recuerda instalar MycasaRenta 2.0 en el teléfono celular que nos diste para contactarte, esto es una medida de seguridad para evitar fraudes contra terceras personas que quieran, perjudicar tu negocio.</p>
        <p><b>3 maneras de obtener MycasaRenta.</b></p>
        <p>
            <ol>
                <li>En Google Play estará la aplicación MycasaRenta 2.0 en caso de que tengas instalada la versión anterior solo dale actualizar, en caso de que sea la primera vez que la vas a instalar, utiliza cualquiera de los hipervínculos de descarga que te hemos hecho llegar en los correos.</li>
                <li>Desde el sitio MycasaParticular.com puedes también descargar e instalar la aplicación MycasaRenta 2.0, en la parte inferior del sitio existe un vínculo a la página de MycasaRenta 2.0.</li>
                <li>Acercarse a la oficina radicada en San Nicolás No. 358 e/ San Rafael y San Miguel, Centro Habana, la Habana, Cuba o llamarnos a los números 78673574/78610147/58419821 que nuestro servicio de atención al cliente se encargará de hacérsela llegar junto a un instructivo de cómo se usa, e incluso explicarle cómo funciona.</li>
            </ol>
        </p>
        <p>MyCasaParticular siempre piensa en ti, realizando cambios constantes para mejorar la comunicación contigo, tratando de hacerte ganar más clientes, más visibilidad en internet y dándote todas las posibilidades para que nos hagas llegar tu disponibilidad.</p>
        <p>Recuerda: No esperes que te olvide, ni olvides que te espero, en este 2017 estelar, actualiza tu disponibilidad en MyCasaParticular.</p>
        <p>
        <br/>
            <b>Equipo MyCasaParticular</b>
        </p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'MYCASARENTA 2.0, LA MANERA FACIL DE TENER + CLIENTES', @contentES)");



    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
