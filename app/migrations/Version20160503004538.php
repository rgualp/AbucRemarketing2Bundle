<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160503004538 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('

        CREATE FUNCTION getClientStatus(
                idClient INTEGER(11),
                reservationDate VARCHAR(25)
            )
            RETURNS INTEGER
        BEGIN
        declare clientStatus integer default 3;
        declare desId integer;
        declare fromDate date;
        declare pending integer;
        declare notAvailable integer;
        declare available integer;
        declare payed integer;
        declare total integer;
        declare totalDestinationsDates integer default 0;
        declare totalAvailableDestinationsDates integer default 0;
        declare totalPayedDestinationsDates integer default 0;
        DECLARE done INT DEFAULT FALSE;

        DECLARE cursor_client CURSOR FOR select o.own_destination, owres.own_res_reservation_from_date,
        SUM(if(gres.gen_res_status = 0, 1, 0)) as pending,
        SUM(if(gres.gen_res_status = 3, 1, 0)) as notAvailable,
        SUM(if(gres.gen_res_status = 1 or gres.gen_res_status = 2 or gres.gen_res_status = 6 or gres.gen_res_status = 8, 1, 0)) as available,
        SUM(if(gres.gen_res_status = 2, 1, 0)) as payed,
        COUNT(gres.gen_res_id) as total
        from ownershipreservation owres
        join generalreservation gres on owres.own_res_gen_res_id = gres.gen_res_id
        join ownership o on gres.gen_res_own_id = o.own_id
        where gres.gen_res_user_id = idClient and gres.gen_res_date = reservationDate
        group by o.own_destination, owres.own_res_reservation_from_date;


        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        open cursor_client;
        select FOUND_ROWS() into totalDestinationsDates ;

        read_loop: LOOP
        FETCH cursor_client INTO desId, fromDate, pending, notAvailable, available, payed, total;
        IF done THEN
              LEAVE read_loop;
            END IF;

        if available >= 1 then
          set totalAvailableDestinationsDates = totalAvailableDestinationsDates + 1;
        END IF;

        if payed >= 1 then
            set totalPayedDestinationsDates = totalPayedDestinationsDates + 1;
        END IF;

        END LOOP read_loop;
        CLOSE cursor_client;

        if totalAvailableDestinationsDates > 0 then
            if totalAvailableDestinationsDates < totalDestinationsDates then
                set clientStatus = 4;
            else
                if totalAvailableDestinationsDates = totalPayedDestinationsDates then
                    set clientStatus = 2;
                else
                    set clientStatus = 1;
                END IF;
            END IF;
        END IF;

        return clientStatus;
        END;
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP FUNCTION IF EXISTS getClientStatus');
    }
}
