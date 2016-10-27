<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161027211841 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES ('Correo Encuesta Turistas MyCP', 'Encuesta_T', CURDATE(), false, 1, 'ROLE_CLIENT_TOURIST')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'Encuesta_T')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name)
                       select DISTINCT @newsletterId, ut.`user_tourist_language`, u.user_email, u.user_user_name  FROM
                       generalreservation gres
                       join user u on gres.gen_res_user_id = u.user_id
                       join usertourist ut on ut.user_tourist_user = u.user_id
                       where u.user_role = 'ROLE_CLIENT_TOURIST'
                       and gres.gen_res_status IN (1, 8)
                       and gres.gen_res_date >= '2016-10-10' and gres.gen_res_date <= '2016-10-23'
                       and (SELECT count(*) from booking b
                       join payment p on b.booking_id = p.booking_id
                       where b.booking_user_id = u.user_id
                       and p.created >= '2016-10-10' and p.created <= '2016-10-23') = 0");

        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");
        $this->addSql("set @langEN = (SELECT min(lang_id) FROM lang WHERE lang_code = 'EN')");
        $this->addSql("set @langDE = (SELECT min(lang_id) FROM lang WHERE lang_code = 'DE')");

        $this->addSql("set @contentES = '<p>En <b>MyCasaParticular.com</b> valoramos el criterio de cada cliente. Nuestro equipo de profesionales trabaja cada día para mejorar los servicios que ofrece el sitio web y deseamos conocer tus opiniones al respecto.</p>
        <p>Ayúdanos a perfeccionar el funcionamiento de nuestra página web respondiendo esta pequeña encuesta. Solo te tomará unos minutos. ¡Gracias!</p>
        <p>Te recuerdo que tu participación es voluntaria y totalmente gratuita. Dinos que piensas:</p>
        <p><a href=\"https://goo.gl/forms/gZ3uu1dbqFm5zR4p2\">https://goo.gl/forms/gZ3uu1dbqFm5zR4p2</a></p>
        <p><br/>Feliz día,<br/><br/><b>Equipo de MyCasaParticular.com</b></p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'MyCasaParticular.com: ayúdanos a mejorar nuestro sitio', @contentES)");

        $this->addSql("set @contentEN = '<p>In <b>MyCasaParticular.com</b> we value the opinion of each client. Our Team of professionals work very day to improve the services of our Website, and we would like to know your comments about it.</p>
        <p>Help us upgrade the functioning of our website by answering this questionnaire. It will only take you a few seconds. Thank you!</p>
        <p>We remind you that you participate of your own will and it is totally free. Let us know what you think:</p>
        <p><a href=\"https://goo.gl/forms/5cuLfbquPXUY4V1V2\">https://goo.gl/forms/5cuLfbquPXUY4V1V2</a></p>
        <p><br/>Have a good day!,<br/><br/><b>MyCasaParticular.com Team</b></p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langEN, 'MyCasaParticular.com: Help us improve our site', @contentEN)");

        $this->addSql("set @contentDE = '<p>Auf <b>MyCasaParticular.com</b> die Meinung der Kunden hat einen großen Wert. Unsere professionelles Team versucht jeden Tag seinen Kundenservice zu verbessern. Deswegen möchten wir Ihre Meinung nach kennen.</p>
        <p>Helfen Sie uns, unsere Website zu optimieren mithilfe dieser Umfrage. Vielen Dank.</p>
        <p>Vergessen Sie nicht dass Ihre Teilnahme, freiwillig und kostenlos ist. Sagen Sie uns was Sie denken:</p>
        <p><a href=\"https://goo.gl/forms/LBf0Z4Am8AuESvWB3\">https://goo.gl/forms/LBf0Z4Am8AuESvWB3</a></p>
        <p><br/>Beste Gruesse,<br/><br/><b>MyCasaParticular.com Team</b></p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langDE, 'MyCasaParticular.com: helfen Sie uns unsere Website zu verbessern', @contentDE)");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
