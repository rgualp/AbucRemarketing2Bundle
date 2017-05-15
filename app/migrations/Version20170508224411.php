<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170508224411 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE accommodation_booking_modality (accommodation INT NOT NULL, price NUMERIC(10, 2) NOT NULL, bookingModalit INT NOT NULL, INDEX IDX_4398B752D385412 (accommodation), INDEX IDX_4398B754E09BC51 (bookingModalit), PRIMARY KEY(accommodation, bookingModalit, price)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking_modality (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE accommodation_booking_modality ADD CONSTRAINT FK_4398B752D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE accommodation_booking_modality ADD CONSTRAINT FK_4398B754E09BC51 FOREIGN KEY (bookingModalit) REFERENCES booking_modality (id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE accommodation_booking_modality DROP FOREIGN KEY FK_4398B754E09BC51');
        $this->addSql('DROP TABLE accommodation_booking_modality');
        $this->addSql('DROP TABLE booking_modality');
    }
}
