<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170207170430 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pa_cancel_payment (id INT AUTO_INCREMENT NOT NULL, type INT NOT NULL, booking INT NOT NULL, user INT NOT NULL, reason VARCHAR(500) NOT NULL, cancel_date DATETIME NOT NULL, give_agency TINYINT(1) NOT NULL, INDEX IDX_3A34E5EC8CDE5729 (type), INDEX IDX_3A34E5ECE00CEDDE (booking), INDEX IDX_3A34E5EC8D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agency_cancel_payment_ownreservation (cancel INT NOT NULL, ownreservation INT NOT NULL, INDEX IDX_F299E2F25616C572 (cancel), INDEX IDX_F299E2F224597F6B (ownreservation), PRIMARY KEY(cancel, ownreservation)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('acpt_from_agency','agencyCancelPaymentType')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'acpt_from_agency' and nom_category = 'agencyCancelPaymentType'), 'De agencia')");

        $this->addSql("insert into nomenclator(nom_name, nom_category) values ('acpt_from_host','agencyCancelPaymentType')");
        $this->addSql("insert into nomenclatorlang(nom_lang_id_lang, nom_lang_id_nomenclator, nom_lang_description) values ((select max(lang_id) from lang where lang_code = 'ES'),(select max(nom_id) from nomenclator where nom_name = 'acpt_from_host' and nom_category = 'agencyCancelPaymentType'), 'De propietario')");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pa_cancel_payment');
        $this->addSql('DROP TABLE agency_cancel_payment_ownreservation');

    }
}
