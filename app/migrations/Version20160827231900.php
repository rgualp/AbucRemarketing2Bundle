<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160827231900 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE postfinance_payment (id INT AUTO_INCREMENT NOT NULL, payment_id INT DEFAULT NULL, created DATETIME DEFAULT NULL, order_id VARCHAR(255) DEFAULT NULL, amount VARCHAR(30) DEFAULT NULL, currency VARCHAR(5) DEFAULT NULL, payment_method VARCHAR(30) DEFAULT NULL, acceptance VARCHAR(50) DEFAULT NULL, status VARCHAR(30) DEFAULT NULL, masked_card_number VARCHAR(30) DEFAULT NULL, payment_reference_id VARCHAR(10) DEFAULT NULL, error_code VARCHAR(30) DEFAULT NULL, card_brand VARCHAR(50) DEFAULT NULL, sha_out_signature VARCHAR(255) DEFAULT NULL, INDEX IDX_D86205464C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE postfinance_payment ADD CONSTRAINT FK_D86205464C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE postfinance_payment');

    }
}
