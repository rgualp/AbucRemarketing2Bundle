<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160525222852 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE owner (id INT AUTO_INCREMENT NOT NULL, municipality INT DEFAULT NULL, province INT DEFAULT NULL, full_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_2 VARCHAR(255) DEFAULT NULL, mobile VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, address_main_street VARCHAR(255) DEFAULT NULL, address_street_number VARCHAR(255) DEFAULT NULL, address_between_1 VARCHAR(255) DEFAULT NULL, address_between_2 VARCHAR(255) DEFAULT NULL, INDEX IDX_CF60E67CC6F56628 (municipality), INDEX IDX_CF60E67C4ADAD40B (province), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE owneraccommodation (accommodation INT NOT NULL, owner INT NOT NULL, INDEX IDX_C0C0BF842D385412 (accommodation), INDEX IDX_C0C0BF84CF60E67C (owner), PRIMARY KEY(accommodation, owner)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE owner ADD CONSTRAINT FK_CF60E67CC6F56628 FOREIGN KEY (municipality) REFERENCES municipality (mun_id)');
        $this->addSql('ALTER TABLE owner ADD CONSTRAINT FK_CF60E67C4ADAD40B FOREIGN KEY (province) REFERENCES province (prov_id)');
        $this->addSql('ALTER TABLE owneraccommodation ADD CONSTRAINT FK_C0C0BF842D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE owneraccommodation ADD CONSTRAINT FK_C0C0BF84CF60E67C FOREIGN KEY (owner) REFERENCES owner (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE owneraccommodation DROP FOREIGN KEY FK_C0C0BF84CF60E67C');
        $this->addSql('DROP TABLE owner');
        $this->addSql('DROP TABLE owneraccommodation');
    }
}
