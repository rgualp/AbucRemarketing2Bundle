<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170403185619 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO newsletter (name, code, creation_date, sent, type, usersRole) VALUES
                      ('CORREO A TURISTAS POLITICA DE CANCELACION', 'TCANCEL', CURDATE(), false, 1, 'ROLE_CLIENT_TOURIST')");
        $this->addSql("set @newsletterId = (SELECT min(id) FROM newsletter WHERE code = 'TCANCEL')");
        $this->addSql("set @langES = (SELECT min(lang_id) FROM lang WHERE lang_code = 'ES')");
        $this->addSql("set @langEN = (SELECT min(lang_id) FROM lang WHERE lang_code = 'EN')");
        $this->addSql("set @langDE = (SELECT min(lang_id) FROM lang WHERE lang_code = 'DE')");
        $this->addSql("set @langFR = (SELECT min(lang_id) FROM lang WHERE lang_code = 'FR')");
        $this->addSql("set @langIT = (SELECT min(lang_id) FROM lang WHERE lang_code = 'IT')");

        $this->addSql("INSERT INTO newsletter_email (newsletter, language, name, email)
                       select distinct @newsletterId, ut.user_tourist_language, u.user_user_name, u.user_email from user u
                       join usertourist ut on ut.user_tourist_user = u.user_id
                       where u.user_enabled = 1;");


        $this->addSql("set @contentES = '
        <p>El equipo de <b>MyCasaParticular</b> actualiza sus <a href=\"https://www.mycasaparticular.com/es/terminos-legales/\">Términos y Condiciones</a> a partir del <b>5 de abril de 2017</b>, para ofrecer mayor garantía a sus clientes.</p>

        <p>
            Consulte la nueva información en los siguientes apartados: <b>Política de precios y Política de cancelaciones y reembolsos</b>.
        </p>
        <p>Recuerda que la información es poder!</p>
        <p>Gracias por elegirnos,</p>
        <p>
        <br/>
            <b>Equipo de MyCasaParticular</b>
        </p>'");
        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langES, 'MyCasaParticular: Actualizamos nuestros Términos y Condiciones', @contentES)");

        $this->addSql("set @contentEN = '
        <p><b>MyCasaParticular</b> team updates its <a href=\"https://www.mycasaparticular.com/en/terms/\">Terms and Conditions</a> from <b>April 5, 2017</b>, to provide greater guarantee to its customers.</p>

        <p>
            Read the new information in the following sections: <b>Price Policy and Cancellation and Refund Policy</b>.
        </p>
        <p>Remember: information is power!</p>
        <p>Thank you for choosing us,</p>
        <p>
        <br/>
            <b>MyCasaParticular.com Team</b>
        </p>'");
        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langEN, 'MyCasaParticular: We update our Terms and Conditions', @contentEN)");

        $this->addSql("set @contentDE = '
        <p><b>MyCasaParticular</b> Team aktualisiert seine <a href=\"https://www.mycasaparticular.com/de/nutzungsbedingungen/\">Geschäftsbedingungen</a> von <b>5 April 2017</b> um mehrere Garantien seiner Kunden anzubieten.</p>

        <p>
            Konsultieren Sie die neue Information in den fogenden Abschnitten: <b>Preispolitik, Stornierungsbedingungen und Rückerstattungen</b>.
        </p>
        <p>Denken Sie daran, dass Information Machtist!</p>
        <p>Vielen Dank </p>
        <p>
        <br/>
            <b>MyCasaParticular Team</b>
        </p>'");
        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langDE, 'MyCasaParticular: Wir aktualisieren unsere AGB', @contentDE)");


        $this->addSql("set @contentFR = '
        <p>L’équipe <b>MyCasaParticular</b> met à jour ses <a href=\"https://www.mycasaparticular.com/fr/nutzungsbedingungen/\">Termes et Conditions</a> du <b>5 Avril 2017</b>, afin d’offrir une plus grande assurance à ses clients.</p>

        <p>
            Veuillez lire les nouvelles informations dans les sections suivantes: <b>politique des prix et conditions d’annulations et de remboursements</b>.
        </p>
        <p>Rappelez-vous que l\'information représente pouvoir!</p>
        <p>Nous vous remercions de nous avoir choisi,</p>
        <p>
        <br/>
            <b>Équipe de MyCasaParticular.com</b>
        </p>'");
        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langFR, 'MyCasaParticular: Nous mettons à jour nos Termes et Conditions', @contentFR)");

        $this->addSql("set @contentIT = '
        <p>Lo staff di <b>MyCasaParticular</b> attualizza i suoi <a href=\"https://www.mycasaparticular.com/it/nutzungsbedingungen/\">Termini e Condizioni</a> a partire dal <b>5 aprile del 2017</b>, per offrire una maggior garanzia ai suoi clienti.</p>

        <p>
            Consulti le nuove informazioni nei seguenti paragrafi: <b>Politica dei prezzi e Politica di cancellazioni e rimborsi</b>.
        </p>
        <p>Ricordi che l’informazione è potere!</p>
        <p>Grazie per sceglierci,</p>
        <p>
        <br/>
            <b>lo Staff di MyCasaParticular.com</b>
        </p>'");
        $this->addSql("INSERT INTO newsletter_content (newsletter, language, subject, emailBody) VALUES (@newsletterId, @langIT, 'MyCasaParticular: Aggiorniamo i nostri Termini e Condizioni', @contentIT)");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
