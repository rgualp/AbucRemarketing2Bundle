<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161025194521 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES ('Correo Casas Particulares de Reserva Inmediata', 'CP_RI', CURDATE(), false, 1, 'ROLE_CLIENT_CASA')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'CP_RI')");
        $this->addSql("set @langId = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'nani6307@nauta.cu', 'Nany')");
        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'natalis8806@gmail.com', 'Natali')");
        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'casacolonialtatyjose@yahoo.com', 'Tatiana')");
        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'luistitle19@yahoo.es', 'Luis Enrique')");
        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'rosalinda86@nauta.cu', 'Yudisleidis')");
        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'yayvilla1936@gmail.com', 'Luis')");
        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'maylenrafa562@gmail.com', 'Maylen')");
        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'maylen.velazquez@nauta.cu', 'Maylen')");
        //$this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'liliaqa@yahoo.es', 'Liliana')");
        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'lacasademima1920@gmail.com', 'Laura')");
        //$this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'jesusmaria2003@yahoo.com', 'Jesus')");
        //$this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'reservation@lacasaflamboyan.com', 'Jesús Manuel')");
        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'moraimaguila@nauta.cu', 'Moraima Alicia')");
        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'sarovi@nauta.cu', 'Sadys')");
        //$this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'raudel@zoltec.co.cu', 'Raudel')");
        //$this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'niky.pmmo@gmail.com', 'Niki')");
        //$this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'reservas@habitacionhabana.co', 'Gertrudis')");
        //$this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'yadirentals@gmail.com', 'Yadami')");
        //$this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'vladymaly16@gmail.com', 'Malinabys')");
        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'robertzap@nauta.cu', 'Roberto')");
        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name) VALUES (@newsletterId, @langId, 'ramos16@nauta.cu', 'Yanelis')");

        $this->addSql("set @content = '
        <p>
            ¿Preparado para recibir más clientes de los que puedes contar? ¡Excelente! En <b>MyCasaParticular.com</b> desarrollamos un nuevo servicio gratuito: la RESERVA INMEDIATA.
        </p>
        <p>¿Cuáles son las ventajas?</p>
        <ul>
            <li>Ahora los huéspedes podrán reservar sin esperar confirmación</li>
            <li>Los viajeros te preferirán. Adoran planificar sus viajes en el menor tiempo posible</li>
            <li>Tu alojamiento tendrá mayor visibilidad en la web de <b>MyCasaParticular.com</b> y una mejor posición en los resultados de búsqueda</li>
            <li>Todo el proceso de reserva se agiliza</li>
            <li>Recibirás más huéspedes en mucho menos tiempo</li>
        </ul>
        <p>¿Sabes qué es lo más estupendo? Te hemos elegido para integrar nuestra lista especial de anfitriones y poner en marcha la RESERVA INMEDIATA.</p>
        <p>Te invitamos a ofrecer este servicio junto a otro selecto grupo de casas particulares en La Habana. ¡Todo un privilegio!</p>
        <p>Nosotros nos encargamos de todo. Solo necesitamos que mantengas actualizado tu calendario de disponibilidad. ¿Cómo puedes hacerlo? Es tan fácil que parece increíble.</p>
        <p>Puedes llamarnos al <b>7 867 3574</b> para informarnos cuando no estás disponible. También puedes escribirnos al correo <a href=\"mailto:info@mycasaparticular.com\">info@mycasaparticular.com</a> o enviarnos un sms al <b>55599750</b> indicando que no estás disponible para una fecha específica.</p>
        <p>Te garantizamos que esta idea será un éxito. Empezaremos a ofrecer el servicio de RESERVA INMEDIATA desde el próximo lunes 31 de octubre. <b>Contáctanos</b> para confirmar si contamos con tu alojamiento.</p>
        <p>Feliz semana,</p>
        <p><b>Equipo de MyCasaParticular.com</b></p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, emailBody) VALUES (@newsletterId, @langId, @content)");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

    }
}
