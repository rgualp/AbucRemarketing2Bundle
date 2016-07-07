<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160622160456 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE servicefee (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, fixedFee NUMERIC(10, 0) NOT NULL, one_nr_until_20_percent NUMERIC(10, 0) NOT NULL, one_nr_from_20_to_25_percent NUMERIC(10, 0) NOT NULL, one_nr_from_more_25_percent NUMERIC(10, 0) NOT NULL, one_night_several_rooms_percent NUMERIC(10, 0) NOT NULL, one_2_nights_percent NUMERIC(10, 0) NOT NULL, one_3_nights_percent NUMERIC(10, 0) NOT NULL, one_4_nights_percent NUMERIC(10, 0) NOT NULL, one_5_nights_percent NUMERIC(10, 0) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE servicefee');
    }
}
