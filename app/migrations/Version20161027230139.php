<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161027230139 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES ('CORREO CLIENTES TURISTAS X INTERRUPCIÓN DE SERVICIO', 'Interrupcion', CURDATE(), false, 1, 'ROLE_CLIENT_TOURIST')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'Interrupcion')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, email, name)
                       select DISTINCT @newsletterId, ut.`user_tourist_language`, u.user_email, u.user_user_name  FROM
                       generalreservation gres
                       join user u on gres.gen_res_user_id = u.user_id
                       join usertourist ut on ut.user_tourist_user = u.user_id
                       where u.user_role = 'ROLE_CLIENT_TOURIST'
                       and gres.gen_res_date >= '2016-10-25'");

        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");
        $this->addSql("set @langEN = (SELECT min(lang_id) FROM lang WHERE lang_code = 'EN')");
        $this->addSql("set @langDE = (SELECT min(lang_id) FROM lang WHERE lang_code = 'DE')");

        $this->addSql("set @contentES = '<p>Sentimos mucho que <b>MyCasaParticular.com</b> estuviera fuera de servicio en las últimas 12 horas &#9785;.</p>
        <p>Ahora todo está en orden y el sitio funciona de manera óptima, por lo que puedes continuar tu proceso de reserva sin problema alguno.</p>
        <p>Realmente lamentamos lo ocurrido. No se repetirá, estamos seguros.</p>
        <p>Mil disculpas nuevamente. ¡Te esperamos en Cuba!</p>
        <p><br/><b>Con <span style=\"font-size: 20px\">&#x2661;</span> Equipo de MyCasaParticular.com</b></p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'MYCASAPARTICULAR.COM FUERA DE SERVICIO', @contentES)");

        $this->addSql("set @contentEN = '<p>We regret that our website <b>MyCasaParticular.com</b> was out of service in the last 12 hours &#9785;.</p>
        <p>Now, everything is ok and the site works perfectly. So, you can continue the reservation process without problems.</p>
        <p>We really regret what happened. That will not be repeated.</p>
        <p>We apologize for the inconvenience and thank you for your understanding.</p>
        <p><br/><b>With <span style=\"font-size: 20px\">&#x2661;</span> MyCasaParticular.com Team</b></p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langEN, 'MYCASAPARTICULAR.COM out of order', @contentEN)");

        $this->addSql("set @contentDE = '<p>Es tut uns sehr Leid dass unsere Website <b>MyCasaParticular.com</b> außer Betrieb während der letzten 12 Stunden war &#9785;.</p>
        <p>Jetzt alles ist in Ordnung  und die Website funktioniert . Deswegen können Sie der Reservierungsprozess weiter führen.</p>
        <p>Wir bitten Sie die Umstaende zu entschuldigen und danken Ihnen fuer Ihr Verstaendnis.Es wird nicht wiederholt.</p>
        <p>Wir warten auf Sie!!</p>
        <p><br/><b><span style=\"font-size: 20px\">&#x2661;</span> MyCasaParticular.com Team</b></p>'");

        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langDE, 'MYCASAPARTICULAR.COM außer Betrieb', @contentDE)");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
