<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130905161201 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, general_reservation_id INT DEFAULT NULL, currency_id INT DEFAULT NULL, created DATETIME NOT NULL, modified DATETIME NOT NULL, payed_amount NUMERIC(10, 0) NOT NULL, status INT NOT NULL, INDEX IDX_6D28840D17D58912 (general_reservation_id), INDEX IDX_6D28840D38248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE skrillPayment (id INT AUTO_INCREMENT NOT NULL, payment_id INT DEFAULT NULL, merchant_email VARCHAR(60) NOT NULL, customer_email VARCHAR(110) NOT NULL, merchant_id VARCHAR(50) NOT NULL, customer_id VARCHAR(50) NOT NULL, merchant_transaction_id VARCHAR(30) NOT NULL, skrill_transaction_id VARCHAR(30) NOT NULL, payed_amount VARCHAR(30) NOT NULL, skrill_currency VARCHAR(5) NOT NULL, status VARCHAR(3) NOT NULL, failed_reason_code VARCHAR(5) NOT NULL, failed_reason_description VARCHAR(255) NOT NULL, md5_signature VARCHAR(64) NOT NULL, merchant_amount VARCHAR(30) NOT NULL, merchant_currency VARCHAR(5) NOT NULL, payment_type VARCHAR(5) NOT NULL, merchant_fields VARCHAR(255) NOT NULL, INDEX IDX_D8DCFFED4C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE payment ADD CONSTRAINT FK_6D28840D17D58912 FOREIGN KEY (general_reservation_id) REFERENCES generalreservation (gen_res_id)");
        $this->addSql("ALTER TABLE payment ADD CONSTRAINT FK_6D28840D38248176 FOREIGN KEY (currency_id) REFERENCES currency (curr_id)");
        $this->addSql("ALTER TABLE skrillPayment ADD CONSTRAINT FK_D8DCFFED4C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)");
        $this->addSql("ALTER TABLE generalreservation ADD gen_res_total_price_in_site INT NOT NULL");
        $this->addSql("ALTER TABLE ownershipreservation ADD own_res_reservation_from_date DATE NOT NULL, ADD own_res_reservation_to_date DATE NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE skrillPayment DROP FOREIGN KEY FK_D8DCFFED4C3A3BB");
        $this->addSql("DROP TABLE payment");
        $this->addSql("DROP TABLE skrillPayment");
        $this->addSql("ALTER TABLE generalreservation DROP gen_res_total_price_in_site");
        $this->addSql("ALTER TABLE ownershipreservation DROP own_res_reservation_from_date, DROP own_res_reservation_to_date");
    }
}
