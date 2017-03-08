<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170130181343 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pa_pending_payment_agency (id INT AUTO_INCREMENT NOT NULL, booking INT NOT NULL, reservation INT NOT NULL, agency INT NOT NULL, status INT DEFAULT NULL, type INT DEFAULT NULL, created_date DATETIME NOT NULL, pay_date DATETIME NOT NULL, amount DOUBLE PRECISION DEFAULT NULL, INDEX IDX_979BE8FAE00CEDDE (booking), INDEX IDX_979BE8FA42C84955 (reservation), INDEX IDX_979BE8FA70C0C6E6 (agency), INDEX IDX_979BE8FA7B00651C (status), INDEX IDX_979BE8FA8CDE5729 (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('cancel_payment_agency','paymentPendingTypeAgency')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'cancel_payment_agency' and nom_category = 'paymentPendingTypeAgency'), 'Cancelación Agencia')");

        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('cancel_payment_accommodation','paymentPendingType')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'cancel_payment_accommodation' and nom_category = 'paymentPendingType'), 'Cancelación Agencia')");

        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('pendingPayment_canceled_status','paymentPendingStatus')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'pendingPayment_canceled_status' and nom_category = 'paymentPendingStatus'), 'Cancelado')");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pa_pending_payment_agency');

    }
}
