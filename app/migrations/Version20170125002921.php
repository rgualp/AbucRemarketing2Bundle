<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170125002921 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP PROCEDURE IF EXISTS sp_calculateServiceTax');

        $this->addSql('
            CREATE PROCEDURE sp_calculateServiceTax ()
            BEGIN
              DECLARE done INT DEFAULT FALSE;
              DECLARE bookingId INT;
              DECLARE rooms INT;
              DECLARE nights INT;
              DECLARE roomPrice DECIMAL(10,2);
              DECLARE serviceFeeTax INT;
              DECLARE bookingCursor CURSOR FOR select
            T.bookingId, SUM(T.rooms) as rooms, SUM(T.nights) as nights,
            ROUND(SUM(T.totalPrice) / (SUM(T.rooms) * SUM(T.nights)), 2) as roomPrice,
            T.serviceFeeTax
            from
            (select owres.own_res_reservation_booking as bookingId, owres.own_res_gen_res_id,
            COUNT(DISTINCT owres.own_res_selected_room_id) as rooms,
            CEIL(SUM(DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`)) / COUNT(DISTINCT owres.own_res_selected_room_id)) as nights,
            SUM(owres.`own_res_total_in_site`) as totalPrice,
            MAX(gres.service_fee) as serviceFeeTax
            from booking b
            join payment p on b.`booking_id` = p.`booking_id`
            join ownershipreservation owres on owres.own_res_reservation_booking = b.`booking_id`
            join generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
            where p.`status` IN (1, 4)
            group by owres.own_res_reservation_booking, owres.own_res_gen_res_id) T
            group by T.bookingId;

              DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

              OPEN bookingCursor;

              read_loop: LOOP
                FETCH bookingCursor INTO bookingId, rooms, nights, roomPrice, serviceFeeTax;
                IF done THEN
                  LEAVE read_loop;
                END IF;

                set @serviceFeeTax = getTaxForServiceWithServiceFee(rooms, nights, roomPrice, serviceFeeTax);

                update booking
                set tax_for_service = @serviceFeeTax
                where booking_id = bookingId;


              END LOOP;
              CLOSE bookingCursor;

            END;
        ');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
