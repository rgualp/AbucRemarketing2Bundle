<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180307134820 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("update servicefee SET current=0  Where servicefee.current = 1 ");
        $this->addSql("INSERT servicefee (date,fixedFee,one_nr_until_20_percent,one_nr_from_20_to_25_percent,one_nr_from_more_25_percent,one_night_several_rooms_percent
,one_2_nights_percent,one_3_nights_percent,one_4_nights_percent,one_5_nights_percent,current)
VALUES (NOW(),0,0,0,0,0,0,0,0,0,1)");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
