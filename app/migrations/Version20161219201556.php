<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161219201556 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP PROCEDURE IF EXISTS calculateRankingYear");

        $this->addSql("
            CREATE PROCEDURE calculateRankingYear(IN yearValue INT)
            BEGIN
                  DECLARE done INT DEFAULT FALSE;
                  DECLARE accommodation INT;
                  DECLARE accommodationsCursor CURSOR FOR select DISTINCT o.own_id from ownership o where o.own_status = 1;

                  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
                  OPEN accommodationsCursor;

                  read_loop: LOOP
                    FETCH accommodationsCursor INTO accommodation;
                    IF done THEN
                      LEAVE read_loop;
                    END IF;

                    set @accommodation = accommodation;
                    set @yearValue = yearValue;
                    set @exists = (SELECT COUNT(*) from ownership_ranking_extra_year rank WHERE rank.accommodation = @accommodation AND rank.year = yearValue);

                    IF @exists = 0 THEN
                       INSERT INTO ownership_ranking_extra_year (accommodation,year,ranking, currentYearFacturation, totalFacturation, totalAvailableRooms,
                       totalNonAvailableRooms, totalReservedRooms, visits, totalAvailableFacturation, totalNonAvailableFacturation)
                       SELECT @accommodation, @yearValue, AVG(rank.ranking), SUM(rank.currentMonthFacturation), SUM(rank.totalFacturation),
                       SUM(rank.totalAvailableRooms), SUM(rank.totalNonAvailableRooms), SUM(rank.totalReservedRooms), SUM(rank.visits), SUM(rank.totalAvailableFacturation),
                       SUM(rank.totalNonAvailableFacturation)
                       FROM ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND YEAR(rank.startDate) = @yearValue;
                    ELSE
                       UPDATE ownership_ranking_extra_year rank
                       SET rank.ranking = (SELECT AVG(r.ranking) FROM ownership_ranking_extra r WHERE r.accommodation = @accommodation AND YEAR(r.startDate) = @yearValue),
                           rank.currentYearFacturation = (SELECT SUM(r.currentMonthFacturation) FROM ownership_ranking_extra r WHERE r.accommodation = @accommodation AND YEAR(r.startDate) = @yearValue),
                           rank.totalFacturation = (SELECT SUM(r.totalFacturation) FROM ownership_ranking_extra r WHERE r.accommodation = @accommodation AND YEAR(r.startDate) = @yearValue),
                           rank.totalAvailableRooms = (SELECT SUM(r.totalAvailableRooms) FROM ownership_ranking_extra r WHERE r.accommodation = @accommodation AND YEAR(r.startDate) = @yearValue),
                           rank.totalNonAvailableRooms = (SELECT SUM(r.totalNonAvailableRooms) FROM ownership_ranking_extra r WHERE r.accommodation = @accommodation AND YEAR(r.startDate) = @yearValue),
                           rank.totalReservedRooms = (SELECT SUM(r.totalReservedRooms) FROM ownership_ranking_extra r WHERE r.accommodation = @accommodation AND YEAR(r.startDate) = @yearValue),
                           rank.visits = (SELECT SUM(r.visits) FROM ownership_ranking_extra r WHERE r.accommodation = @accommodation AND YEAR(r.startDate) = @yearValue),
                           rank.totalAvailableFacturation = (SELECT SUM(r.totalAvailableFacturation) FROM ownership_ranking_extra r WHERE r.accommodation = @accommodation AND YEAR(r.startDate) = @yearValue),
                           rank.totalNonAvailableFacturation = (SELECT SUM(r.totalNonAvailableFacturation) FROM ownership_ranking_extra r WHERE r.accommodation = @accommodation AND YEAR(r.startDate) = @yearValue)
                       WHERE rank.accommodation = @accommodation AND rank.year = @yearValue;
                    END IF;


                    /*Colocar la categoria segun el ranking del mes*/
                    set @ranking = (SELECT rank.ranking FROM ownership_ranking_extra_year rank WHERE rank.accommodation = @accommodation AND rank.year = @yearValue LIMIT 1);
                    set @goldType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'rankingCategory' AND nom.nom_name = 'gold_ranking');
                    set @silverType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'rankingCategory' AND nom.nom_name = 'silver_ranking');
                    set @bronzeType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'rankingCategory' AND nom.nom_name = 'bronze_ranking');
                    set @amateurType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'rankingCategory' AND nom.nom_name = 'amateur_ranking');

                    set @category = IF(@ranking < 50, @amateurType, IF(@ranking >= 50 and @ranking < 140, @bronzeType, IF(@ranking >= 140 and @ranking < 360, @silverType, @goldType )));

                    UPDATE ownership_ranking_extra_year rank
                    SET rank.category = @category
                    WHERE rank.accommodation = @accommodation AND rank.year = @yearValue;

                  END LOOP;
                  CLOSE accommodationsCursor;
            END;
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP PROCEDURE IF EXISTS calculateRankingYear");
    }
}
