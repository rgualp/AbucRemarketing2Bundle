<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130910105444 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE favorite (favorite_id INT AUTO_INCREMENT NOT NULL, favorite_user INT DEFAULT NULL, favorite_ownership INT DEFAULT NULL, favorite_destination INT DEFAULT NULL, favorite_session_id VARCHAR(255) DEFAULT NULL, favorite_creation_date DATETIME DEFAULT NULL, INDEX IDX_68C58ED96395CF76 (favorite_user), INDEX IDX_68C58ED9D2807906 (favorite_ownership), INDEX IDX_68C58ED9FDD47BE9 (favorite_destination), PRIMARY KEY(favorite_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED96395CF76 FOREIGN KEY (favorite_user) REFERENCES user (user_id)");
        $this->addSql("ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9D2807906 FOREIGN KEY (favorite_ownership) REFERENCES ownership (own_id)");
        $this->addSql("ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9FDD47BE9 FOREIGN KEY (favorite_destination) REFERENCES destination (des_id)");
        $this->addSql("ALTER TABLE payment CHANGE created created DATETIME DEFAULT NULL, CHANGE modified modified DATETIME DEFAULT NULL, CHANGE payed_amount payed_amount NUMERIC(10, 0) DEFAULT NULL, CHANGE status status INT DEFAULT NULL");
        $this->addSql("ALTER TABLE skrillpayment CHANGE merchant_email merchant_email VARCHAR(60) DEFAULT NULL, CHANGE customer_email customer_email VARCHAR(110) DEFAULT NULL, CHANGE merchant_id merchant_id VARCHAR(50) DEFAULT NULL, CHANGE customer_id customer_id VARCHAR(50) DEFAULT NULL, CHANGE merchant_transaction_id merchant_transaction_id VARCHAR(30) DEFAULT NULL, CHANGE skrill_transaction_id skrill_transaction_id VARCHAR(30) DEFAULT NULL, CHANGE payed_amount payed_amount VARCHAR(30) DEFAULT NULL, CHANGE skrill_currency skrill_currency VARCHAR(5) DEFAULT NULL, CHANGE status status VARCHAR(3) DEFAULT NULL, CHANGE failed_reason_code failed_reason_code VARCHAR(5) DEFAULT NULL, CHANGE failed_reason_description failed_reason_description VARCHAR(255) DEFAULT NULL, CHANGE md5_signature md5_signature VARCHAR(64) DEFAULT NULL, CHANGE merchant_amount merchant_amount VARCHAR(30) DEFAULT NULL, CHANGE merchant_currency merchant_currency VARCHAR(5) DEFAULT NULL, CHANGE payment_type payment_type VARCHAR(5) DEFAULT NULL, CHANGE merchant_fields merchant_fields VARCHAR(255) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE favorite");
        $this->addSql("ALTER TABLE payment CHANGE created created DATETIME NOT NULL, CHANGE modified modified DATETIME NOT NULL, CHANGE payed_amount payed_amount NUMERIC(10, 0) NOT NULL, CHANGE status status INT NOT NULL");
        $this->addSql("ALTER TABLE skrillpayment CHANGE merchant_email merchant_email VARCHAR(60) NOT NULL, CHANGE customer_email customer_email VARCHAR(110) NOT NULL, CHANGE merchant_id merchant_id VARCHAR(50) NOT NULL, CHANGE customer_id customer_id VARCHAR(50) NOT NULL, CHANGE merchant_transaction_id merchant_transaction_id VARCHAR(30) NOT NULL, CHANGE skrill_transaction_id skrill_transaction_id VARCHAR(30) NOT NULL, CHANGE payed_amount payed_amount VARCHAR(30) NOT NULL, CHANGE skrill_currency skrill_currency VARCHAR(5) NOT NULL, CHANGE status status VARCHAR(3) NOT NULL, CHANGE failed_reason_code failed_reason_code VARCHAR(5) NOT NULL, CHANGE failed_reason_description failed_reason_description VARCHAR(255) NOT NULL, CHANGE md5_signature md5_signature VARCHAR(64) NOT NULL, CHANGE merchant_amount merchant_amount VARCHAR(30) NOT NULL, CHANGE merchant_currency merchant_currency VARCHAR(5) NOT NULL, CHANGE payment_type payment_type VARCHAR(5) NOT NULL, CHANGE merchant_fields merchant_fields VARCHAR(255) NOT NULL");
    }
}
