<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170516230037 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE accommodation_booking_modality DROP FOREIGN KEY FK_4398B754E09BC51');
        $this->addSql('DROP INDEX IDX_4398B754E09BC51 ON accommodation_booking_modality');
        $this->addSql('ALTER TABLE accommodation_booking_modality DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE accommodation_booking_modality CHANGE bookingmodalit bookingModality INT NOT NULL');
        $this->addSql('ALTER TABLE accommodation_booking_modality ADD CONSTRAINT FK_4398B75E7F94ECB FOREIGN KEY (bookingModality) REFERENCES booking_modality (id)');
        $this->addSql('CREATE INDEX IDX_4398B75E7F94ECB ON accommodation_booking_modality (bookingModality)');
        $this->addSql('ALTER TABLE accommodation_booking_modality ADD PRIMARY KEY (accommodation, bookingModality, price)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE accommodation_booking_modality DROP FOREIGN KEY FK_4398B75E7F94ECB');
        $this->addSql('DROP INDEX IDX_4398B75E7F94ECB ON accommodation_booking_modality');
        $this->addSql('ALTER TABLE accommodation_booking_modality DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE accommodation_booking_modality CHANGE bookingmodality bookingModalit INT NOT NULL');
        $this->addSql('ALTER TABLE accommodation_booking_modality ADD CONSTRAINT FK_4398B754E09BC51 FOREIGN KEY (bookingModalit) REFERENCES booking_modality (id)');
        $this->addSql('CREATE INDEX IDX_4398B754E09BC51 ON accommodation_booking_modality (bookingModalit)');
        $this->addSql('ALTER TABLE accommodation_booking_modality ADD PRIMARY KEY (accommodation, bookingModalit, price)');

    }
}
