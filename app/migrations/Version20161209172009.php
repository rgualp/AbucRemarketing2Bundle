<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161209172009 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP PROCEDURE IF EXISTS calculateRanking");
        //Triggers en ownership
        $this->addSql("
            CREATE PROCEDURE calculateRanking(IN monthValue INT, IN yearValue INT)
            BEGIN
                CALL calculateRankingVariables (monthValue, yearValue);
                CALL calculatePlacesByDestination (monthValue, yearValue);
            END;
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP PROCEDURE IF EXISTS calculateRanking");

    }
}
