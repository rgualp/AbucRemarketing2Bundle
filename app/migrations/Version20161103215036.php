<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161103215036 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES ('CORREO A PROPIETARIOS QUE INCUMPLEN LAS NORMAS DE MYCP', 'PROP_IN', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'PROP_IN')");

        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select DISTINCT @newsletterId, @langES, IF(o.own_homeowner_1 IS NOT NULL, o.own_homeowner_1, o.own_homeowner_2),
                       IF((o.own_email_1 IS NOT NULL AND o.own_email_1 <> ''), o.own_email_1, o.own_email_2)
                       from ownership o
                       WHERE (o.own_email_1 IS NOT NULL AND o.own_email_1 <> '') OR (o.own_email_2 IS NOT NULL AND o.own_email_2 <> '');");


        $this->addSql("set @contentES = '<p>En <b>MyCasaParticular.com</b> promovemos el desarrollo de un turismo responsable, de intercambio positivo y honesto entre nuestros clientes.</p>
        <p>Cada una de las acciones del equipo están encaminadas a lograr la satisfacción de los usuarios. Nuestro compromiso es con ustedes y con los viajeros.</p>
        <p>Cualquier irrespeto a las reglas que establecimos en <b>MyCasaParticular.com</b> deteriora nuestra imagen. Con estas acciones, todos perdemos credibilidad entre los usuarios e inevitablemente disminuyen las reservaciones.</p>
        <p>Es de interés común asumir las normas que el sitio establece para sus clientes anfitriones y turistas. De otro modo, las ganancias se verían realmente afectadas.</p>
        <p>Nuestro trabajo es asegurar a los turistas la mejor estancia en Cuba y ellos desean disfrutar y pagar por el alojamiento que han elegido en Internet y no por otro.</p>
        <p>Te invitamos a respetar las reservas de los viajeros y a mantener actualizado tu calendario de disponibilidad. Para esta operación, <b>MyCasaParticular.com</b> ha creado la aplicación móvil gratuita MyCasaRenta, que te permitirá mantener al día la ocupación de tus habitaciones. </p>
        <p>Recuerda que un comentario negativo o una opinión desfavorable pueden dar al traste con el trabajo que desde hace años realizamos de conjunto.</p>
        <p><b>MyCasaParticular.com</b> somos todos. Te lo pedimos con <span style=\"font-size: 20px\">&#x2661;</span></p>
        <p><br/>Buen fin de semana,<br/><br/><b>Equipo de MyCasaParticular.com</b></p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'MyCasaParticular.com-Alertamos a nuestros anfitriones', @contentES)");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
