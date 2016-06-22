<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160622174038 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE servicefee CHANGE fixedFee fixedFee NUMERIC(10, 2) NOT NULL, CHANGE one_nr_from_20_to_25_percent one_nr_from_20_to_25_percent NUMERIC(10, 2) NOT NULL, CHANGE one_nr_from_more_25_percent one_nr_from_more_25_percent NUMERIC(10, 2) NOT NULL, CHANGE one_night_several_rooms_percent one_night_several_rooms_percent NUMERIC(10, 2) NOT NULL, CHANGE one_2_nights_percent one_2_nights_percent NUMERIC(10, 2) NOT NULL, CHANGE one_3_nights_percent one_3_nights_percent NUMERIC(10, 2) NOT NULL, CHANGE one_4_nights_percent one_4_nights_percent NUMERIC(10, 2) NOT NULL, CHANGE one_5_nights_percent one_5_nights_percent NUMERIC(10, 2) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE servicefee CHANGE fixedFee fixedFee NUMERIC(10, 0) NOT NULL, CHANGE one_nr_from_20_to_25_percent one_nr_from_20_to_25_percent NUMERIC(10, 0) NOT NULL, CHANGE one_nr_from_more_25_percent one_nr_from_more_25_percent NUMERIC(10, 0) NOT NULL, CHANGE one_night_several_rooms_percent one_night_several_rooms_percent NUMERIC(10, 0) NOT NULL, CHANGE one_2_nights_percent one_2_nights_percent NUMERIC(10, 0) NOT NULL, CHANGE one_3_nights_percent one_3_nights_percent NUMERIC(10, 0) NOT NULL, CHANGE one_4_nights_percent one_4_nights_percent NUMERIC(10, 0) NOT NULL, CHANGE one_5_nights_percent one_5_nights_percent NUMERIC(10, 0) NOT NULL');
    }
}
