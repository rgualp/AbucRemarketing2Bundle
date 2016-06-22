<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160622174240 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("insert servicefee (date, fixedFee, one_nr_until_20_percent, one_nr_from_20_to_25_percent, one_nr_from_more_25_percent, one_night_several_rooms_percent, one_2_nights_percent, one_3_nights_percent, one_4_nights_percent, one_5_nights_percent, current)
            values ('2014-04-20', 10, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            (CURDATE(), 5, 0.4, 0.3, 0.25, 0.25, 0.2, 0.15, 0.12, 0.1, 1)");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
