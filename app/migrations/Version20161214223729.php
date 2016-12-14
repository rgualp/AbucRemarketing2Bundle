<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161214223729 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("DROP PROCEDURE IF EXISTS calculateRankingVariables");
        //Triggers en ownership
        $this->addSql("
        CREATE PROCEDURE calculateRankingVariables(IN monthValue INT, IN yearValue INT)
        BEGIN
          DECLARE done INT DEFAULT FALSE;
          DECLARE accommodation INT;
          DECLARE rr INT;
          DECLARE ri INT;
          DECLARE accommodationsCursor CURSOR FOR select DISTINCT o.own_id, o.own_inmediate_booking, o.own_inmediate_booking_2 from ownership o where o.own_status = 1;

          DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

          set @date = DATE_ADD(MAKEDATE(yearValue, 1), INTERVAL (monthValue)-1 MONTH);

          set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(@date),INTERVAL DAY(LAST_DAY(@date))-1 DAY);
          set @lastOfCurrentMonth = LAST_DAY(@date);
          set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);

          OPEN accommodationsCursor;

          read_loop: LOOP
            FETCH accommodationsCursor INTO accommodation, rr, ri;
            IF done THEN
              LEAVE read_loop;
            END IF;

            set @date = DATE_ADD(MAKEDATE(yearValue, 1), INTERVAL (monthValue)-1 MONTH);

            set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(@date),INTERVAL DAY(LAST_DAY(@date))-1 DAY);
            set @lastOfCurrentMonth = LAST_DAY(@date);
            set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);

            set @accommodation = accommodation;
            set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

            /*Premio*/
            set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = accommodation AND aa.year = YEAR(@date));
            set @awards = IF(@awards >= 1, 5, 0);

            /*Penalizaciones*/
            set @penalties = (SELECT COUNT(*) FROM penalty WHERE ((creationDate >= @firstOfCurrentMonth AND creationDate <= @lastOfCurrentMonth) OR (finalizationDate >= @firstOfCurrentMonth AND finalizationDate <= @lastOfCurrentMonth)) AND accommodation = @accommodation);
            set @penaltiesRanking = IF(@penalties > 0, 5, 0);

            /*Facturacion*/
            set @totalFacturation = (select COALESCE(SUM(IF((owres.own_res_night_price is null or owres.own_res_night_price = 0) , COALESCE(owres.own_res_total_in_site, 0), owres.own_res_night_price * DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date))), 0) as total
                          from generalreservation gres
                          join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
                          join payment p on p.booking_id = owres.own_res_reservation_booking
                          where gres.gen_res_id NOT IN (select f.reservation from failure f where f.accommodation = @accommodation )
                          and gres.gen_res_own_id = @accommodation
                          and gres.gen_res_status = 2
                          and gres.gen_res_date >= @firstOfCurrentMonth and gres.gen_res_date <= @lastOfCurrentMonth
                          and p.status in (1, 4));
            set @percent = (SELECT o.own_commission_percent / 100 from ownership o where o.own_id = @accommodation LIMIT 1);
            set @totalFacturation =  @totalFacturation*(1-@percent);
            set @faRanking = IF(@totalFacturation = 0, 0, IF(@totalFacturation < 250, 1, IF(@totalFacturation >= 450, 5, 2)));

            /*Fallos casa y turistas (2)*/
            set @accommodationFailureType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'failureType' AND nom.nom_name = 'accommodation_failure');
            set @touristFailureType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'failureType' AND nom.nom_name = 'tourist_failure');
            set @accommodationFailures = (SELECT COUNT(fail.id) FROM failure fail WHERE fail.creationDate >= @firstOfCurrentMonth AND fail.creationDate <= @lastOfCurrentMonth AND fail.accommodation = @accommodation AND fail.type = @accommodationFailureType);
            set @touristFailures = (SELECT COUNT(fail.id) FROM failure fail WHERE fail.creationDate >= @firstOfCurrentMonth AND fail.creationDate <= @lastOfCurrentMonth AND fail.accommodation = @accommodation AND fail.type = @touristFailureType);
            set @accommodationFailuresRanking = IF(@accommodationFailures = 0, 0, IF(@accommodationFailures = 1, 1, IF(@accommodationFailures >= 3, 5, 3)));
            set @touristFailuresRanking = IF(@touristFailures = 0, 0, IF(@touristFailures = 1, 1, IF(@touristFailures >= 3, 5, 3)));

            /*Nuevas Ofertas*/
            set @totalNewOffers = (SELECT COUNT(*) FROM generalreservation gres JOIN offerlog offer on gres.gen_res_id = offer.log_offer_reservation WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation);
            set @payedNewOffers = (SELECT COUNT(DISTINCT gres.gen_res_id) FROM generalreservation gres
                                   JOIN ownershipreservation owres on gres.gen_res_id = owres.own_res_gen_res_id
                                   JOIN offerlog offer on gres.gen_res_id = offer.log_offer_reservation
                                   JOIN payment p on p.booking_id = owres.own_res_reservation_booking
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND gres.gen_res_status = 2 );
            set @payedNewOffersPercent = COALESCE((@payedNewOffers / @totalNewOffers) * 100, 0);
            set @payedNewOffersRanking = IF(@payedNewOffersPercent = 0, 0, IF(@payedNewOffersPercent = 100, 5, IF(@payedNewOffersPercent <= 33, 3, 1)));

            /*Comentarios positivos y negativos (2)*/
            set @totalComments = (SELECT COUNT(*) FROM comment c WHERE c.com_date >= @firstOfCurrentMonth AND c.com_date <= @lastOfCurrentMonth AND c.com_ownership = @accommodation AND c.com_public = 1 AND c.com_user IN (select distinct gres.gen_res_user_id from generalreservation gres where gres.gen_res_status = 2));
            set @positive = (SELECT COUNT(*) FROM comment c WHERE c.com_date >= @firstOfCurrentMonth AND c.com_date <= @lastOfCurrentMonth AND c.com_ownership = @accommodation AND c.positive = 1 and c.com_public = 1 AND c.com_user IN (select distinct gres.gen_res_user_id from generalreservation gres where gres.gen_res_status = 2));
            set @negative = (SELECT COUNT(*) FROM comment c WHERE c.com_date >= @firstOfCurrentMonth AND c.com_date <= @lastOfCurrentMonth AND c.com_ownership = @accommodation AND c.positive = 0 and c.com_public = 1 AND c.com_user IN (select distinct gres.gen_res_user_id from generalreservation gres where gres.gen_res_status = 2));
            set @positivePercent = COALESCE((@positive / @totalComments) * 100, 0);
            set @negativePercent = COALESCE((@negative / @totalComments) * 100, 0);
            set @positiveRanking = IF(@totalComments = 0, 0, IF(@positivePercent = 100, 5, IF(@totalComments = 1, 1, 3)));
            set @negativeRanking = IF(@negative = 0, 0, IF(@negativePercent >= 50, 5, IF(@negativePercent < 33, 1, 3)));

            /*Reservas*/
            set @totalSolicitudesDisponibles = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation AND owres.own_res_status IN (1, 2, 4, 5, 6) AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer));
            set @reservations = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                 JOIN payment p on p.booking_id = owres.own_res_reservation_booking
                                 WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                 AND owres.own_res_status = 5 and p.status in (1, 4) AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer));
            set @reservationsPercent = COALESCE((@reservations / @totalSolicitudesDisponibles) * 100, 0);
            set @reservationsRanking = IF(@reservationsPercent = 0, 0, IF(@reservationsPercent = 100, 5, IF(@reservationsPercent <= 33, 3, 1)));

            /*Solicitudes disponibles y no disponibles (2)*/
            set @totalSolicitudes = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                    WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer));

            /*set @sd = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                       WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                       AND owres.own_res_status IN (1, 2, 4, 5, 6) AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer));*/
            set @snd = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                        WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                        AND owres.own_res_status = 3 AND gres.gen_res_id NOT IN (SELECT offer.log_offer_reservation FROM offerlog offer));
            set @sdPercent = COALESCE((@totalSolicitudesDisponibles / @totalSolicitudes) * 100, 0);
            set @sndPercent = COALESCE((@snd / @totalSolicitudes) * 100, 0);
            set @sdRanking = IF(@sdPercent = 100, 5, IF(@sdPercent >= 50, 3, IF(@sdPercent < 33, 0, 1)));
            set @sndRanking = IF(@snd = 0, 0, IF(@sndPercent >= 33, 5, IF(@sndPercent < 33 and @snd > 1, 3, 1)));

            /*Reserva Rápida, Reserva Inmediata*/
            set @rrRanking = IF(rr = 1, 5, 0);
            set @riRanking = IF(ri = 1, 5, 0);

            /*Frecuencia de actualización del calendario*/
            set @totalFAD = (select COUNT(*) FROM accommodation_calendar_frequency freq where MONTH(freq.updatedDate) = monthValue  and freq.accommodation = @accommodation);
            set @fadRanking = IF(@totalFAD = 0, 0, IF(@totalFAD > 0 AND @totalFAD < 4, 1, IF(@total >= 4 AND @total < 25, 3, 5)));

            /*Confianza*/
            set @confidence = COALESCE((SELECT o.confidence FROM ownership o WHERE o.own_id = @accommodation LIMIT 1), 0);
            set @totalRanking = COALESCE((select sum(COALESCE(rank.ranking, 0)) from ownership_ranking_extra rank where rank.accommodation = @accommodation and rank.endDate < @firstOfCurrentMonth order by rank.endDate DESC LIMIT 12), 0);
            set @confidenceRanking = IF(@confidence = 1, 5, IF(@totalRanking is null or @totalRanking < 1200, 0, IF(@totalRanking>=1200 and @totalRanking < 3840, 1, IF(@totalRanking >= 5520, 5, 3))));

            IF @exists = 0 THEN
               INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,rankingPoints, awards, penalties, facturation,
               failureCasa, failureClients, newOffersReserved,positiveComments, negativeComments, reservations, sd,snd, rr, ri, fad, confidence)
               VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @rankingPoints, @awards, @penaltiesRanking, @faRanking,
               @accommodationFailuresRanking, @touristFailuresRanking, @payedNewOffersRanking, @positiveRanking, @negativeRanking, @reservationsRanking, @sdRanking,
               @sndRanking, @rrRanking, @riRanking, @fadRanking, @confidenceRanking);
            ELSE
               UPDATE ownership_ranking_extra rank
               SET rank.penalties = @penaltiesRanking,
                   rank.awards = @awards,
                   rank.facturation = @faRanking,
                   rank.failureCasa = @accommodationFailuresRanking,
                   rank.failureClients = @touristFailuresRanking,
                   rank.newOffersReserved = @payedNewOffersRanking,
                   rank.positiveComments = @positiveRanking,
                   rank.negativeComments = @negativeRanking,
                   rank.reservations = @reservationsRanking,
                   rank.sd = @sdRanking,
                   rank.snd = @sndRanking,
                   rank.rr = @rrRanking,
                   rank.ri = @riRanking,
                   rank.fad = @fadRanking,
                   rank.confidence = @confidenceRanking
               WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
            END IF;

            /*Calcular ranking - FINALLY!!*/
            UPDATE ownership_ranking_extra rank
            JOIN ranking_point p ON rank.rankingPoints = p.id
            SET rank.ranking = COALESCE(rank.awards * p.awards, 0) + COALESCE(rank.facturation * p.facturation, 0) + COALESCE(rank.fad * p.fad, 0) +
                                  COALESCE(rank.failureCasa * p.failureCasa, 0) + COALESCE(rank.failureClients * p.failureClients, 0) +
                                  COALESCE(rank.negativeComments * p.negativeComments, 0) + COALESCE(rank.newOffersReserved * p.newOffers, 0) +
                                  COALESCE(rank.penalties * p.penalties, 0) + COALESCE(rank.positiveComments * p.positiveComments, 0) +
                                  COALESCE(rank.reservations * p.reservations, 0) + COALESCE(rank.ri * p.ri, 0) + COALESCE(rank.rr * p.rr, 0) +
                                  COALESCE(rank.sd * p.sd, 0) + COALESCE(rank.snd * p.snd, 0) + COALESCE(rank.confidence * p.confidence, 0)
            WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;

            /*Colocar la categoria segun el ranking del mes*/
            set @ranking = (SELECT rank.ranking FROM ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth LIMIT 1);
            set @goldType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'rankingCategory' AND nom.nom_name = 'gold_ranking');
            set @silverType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'rankingCategory' AND nom.nom_name = 'silver_ranking');
            set @bronzeType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'rankingCategory' AND nom.nom_name = 'bronze_ranking');
            set @amateurType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'rankingCategory' AND nom.nom_name = 'amateur_ranking');

            set @category = IF(@ranking < 50, @amateurType, IF(@ranking >= 50 and @ranking < 140, @bronzeType, IF(@ranking >= 140 and @ranking < 360, @silverType, @goldType )));

            UPDATE ownership_ranking_extra rank
            SET rank.category = @category
            WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;

            UPDATE ownership o
            SET o.own_ranking = @ranking
            WHERE o.own_id = @accommodation;

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
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("DROP PROCEDURE IF EXISTS calculateRankingVariables");
        //Triggers en ownership
        $this->addSql("
        CREATE PROCEDURE calculateRankingVariables(IN monthValue INT, IN yearValue INT)
        BEGIN
          DECLARE done INT DEFAULT FALSE;
          DECLARE accommodation INT;
          DECLARE rr INT;
          DECLARE ri INT;
          DECLARE accommodationsCursor CURSOR FOR select DISTINCT o.own_id, o.own_inmediate_booking, o.own_inmediate_booking_2 from ownership o where o.own_status = 1;

          DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

          set @date = DATE_ADD(MAKEDATE(yearValue, 1), INTERVAL (monthValue)-1 MONTH);

          set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(@date),INTERVAL DAY(LAST_DAY(@date))-1 DAY);
          set @lastOfCurrentMonth = LAST_DAY(@date);
          set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);

          OPEN accommodationsCursor;

          read_loop: LOOP
            FETCH accommodationsCursor INTO accommodation, rr, ri;
            IF done THEN
              LEAVE read_loop;
            END IF;

            set @date = DATE_ADD(MAKEDATE(yearValue, 1), INTERVAL (monthValue)-1 MONTH);

            set @firstOfCurrentMonth = DATE_SUB(LAST_DAY(@date),INTERVAL DAY(LAST_DAY(@date))-1 DAY);
            set @lastOfCurrentMonth = LAST_DAY(@date);
            set @rankingPoints = (SELECT id FROM ranking_point WHERE active = 1 ORDER BY creationDate DESC LIMIT 1);

            set @accommodation = accommodation;
            set @exists = (SELECT COUNT(*) from ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth);

            /*Premio*/
            set @awards = (SELECT COUNT(*) FROM accommodation_award aa WHERE aa.accommodation = accommodation AND aa.year = YEAR(@date));
            set @awards = IF(@awards >= 1, 5, 0);

            /*Penalizaciones*/
            set @penalties = (SELECT COUNT(*) FROM penalty WHERE ((creationDate >= @firstOfCurrentMonth AND creationDate <= @lastOfCurrentMonth) OR (finalizationDate >= @firstOfCurrentMonth AND finalizationDate <= @lastOfCurrentMonth)) AND accommodation = @accommodation);
            set @penaltiesRanking = IF(@penalties > 0, 5, 0);

            /*Facturacion*/
            set @totalFacturation = (select COALESCE(SUM(IF((owres.own_res_night_price is null or owres.own_res_night_price = 0) , COALESCE(owres.own_res_total_in_site, 0), owres.own_res_night_price * DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date))), 0) as total
                          from generalreservation gres
                          join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
                          join payment p on p.booking_id = owres.own_res_reservation_booking
                          where gres.gen_res_id NOT IN (select f.reservation from failure f where f.accommodation = @accommodation )
                          and gres.gen_res_own_id = @accommodation
                          and gres.gen_res_status = 2
                          and gres.gen_res_date >= @firstOfCurrentMonth and gres.gen_res_date <= @lastOfCurrentMonth
                          and p.status in (1, 4));
            set @percent = (SELECT o.own_commission_percent / 100 from ownership o where o.own_id = @accommodation LIMIT 1);
            set @totalFacturation =  @totalFacturation*(1-@percent);
            set @faRanking = IF(@totalFacturation = 0, 0, IF(@totalFacturation < 250, 1, IF(@totalFacturation >= 450, 5, 2)));

            /*Fallos casa y turistas (2)*/
            set @accommodationFailureType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'failureType' AND nom.nom_name = 'accommodation_failure');
            set @touristFailureType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'failureType' AND nom.nom_name = 'tourist_failure');
            set @accommodationFailures = (SELECT COUNT(fail.id) FROM failure fail WHERE fail.creationDate >= @firstOfCurrentMonth AND fail.creationDate <= @lastOfCurrentMonth AND fail.accommodation = @accommodation AND fail.type = @accommodationFailureType);
            set @touristFailures = (SELECT COUNT(fail.id) FROM failure fail WHERE fail.creationDate >= @firstOfCurrentMonth AND fail.creationDate <= @lastOfCurrentMonth AND fail.accommodation = @accommodation AND fail.type = @touristFailureType);
            set @accommodationFailuresRanking = IF(@accommodationFailures = 0, 0, IF(@accommodationFailures = 1, 1, IF(@accommodationFailures >= 3, 5, 3)));
            set @touristFailuresRanking = IF(@touristFailures = 0, 0, IF(@touristFailures = 1, 1, IF(@touristFailures >= 3, 5, 3)));

            /*Nuevas Ofertas*/
            set @totalNewOffers = (SELECT COUNT(*) FROM generalreservation gres JOIN offerlog offer on gres.gen_res_id = offer.log_offer_reservation WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation);
            set @payedNewOffers = (SELECT COUNT(DISTINCT gres.gen_res_id) FROM generalreservation gres
                                   JOIN ownershipreservation owres on gres.gen_res_id = owres.own_res_gen_res_id
                                   JOIN offerlog offer on gres.gen_res_id = offer.log_offer_reservation
                                   JOIN payment p on p.booking_id = owres.own_res_reservation_booking
                                   WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                   AND gres.gen_res_status = 2 );
            set @payedNewOffersPercent = COALESCE((@payedNewOffers / @totalNewOffers) * 100, 0);
            set @payedNewOffersRanking = IF(@payedNewOffersPercent = 0, 0, IF(@payedNewOffersPercent = 100, 5, IF(@payedNewOffersPercent <= 33, 3, 1)));

            /*Comentarios positivos y negativos (2)*/
            set @totalComments = (SELECT COUNT(*) FROM comment c WHERE c.com_date >= @firstOfCurrentMonth AND c.com_date <= @lastOfCurrentMonth AND c.com_ownership = @accommodation AND c.com_public = 1 AND c.com_user IN (select distinct gres.gen_res_user_id from generalreservation gres where gres.gen_res_status = 2));
            set @positive = (SELECT COUNT(*) FROM comment c WHERE c.com_date >= @firstOfCurrentMonth AND c.com_date <= @lastOfCurrentMonth AND c.com_ownership = @accommodation AND c.positive = 1 and c.com_public = 1 AND c.com_user IN (select distinct gres.gen_res_user_id from generalreservation gres where gres.gen_res_status = 2));
            set @negative = @totalComments - @positive;
            set @positivePercent = COALESCE((@positive / @totalComments) * 100, 0);
            set @negativePercent = COALESCE((@negative / @totalComments) * 100, 0);
            set @positiveRanking = IF(@totalComments = 0, 0, IF(@positivePercent = 100, 5, IF(@totalComments = 1, 1, 3)));
            set @negativeRanking = IF(@negative = 0, 0, IF(@negativePercent >= 50, 5, IF(@negativePercent < 33, 1, 3)));

            /*Reservas*/
            set @totalSolicitudesDisponibles = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation AND owres.own_res_status IN (1, 2, 4, 5, 6));
            set @reservations = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                 JOIN payment p on p.booking_id = owres.own_res_reservation_booking
                                 WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                                 AND owres.own_res_status = 5 and p.status in (1, 4));
            set @reservationsPercent = COALESCE((@reservations / @totalSolicitudesDisponibles) * 100, 0);
            set @reservationsRanking = IF(@reservationsPercent = 0, 0, IF(@reservationsPercent = 100, 5, IF(@reservationsPercent <= 33, 3, 1)));

            /*Solicitudes disponibles y no disponibles (2)*/
            set @totalSolicitudes = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                                    WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation);

            /*set @sd = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                       WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                       AND owres.own_res_status IN (1, 2, 4, 5, 6) );*/
            set @snd = (SELECT COUNT(*) FROM ownershipreservation owres JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                        WHERE gres.gen_res_date >= @firstOfCurrentMonth AND gres.gen_res_date <= @lastOfCurrentMonth AND gres.gen_res_own_id = @accommodation
                        AND owres.own_res_status = 3 );
            set @sdPercent = COALESCE((@totalSolicitudesDisponibles / @totalSolicitudes) * 100, 0);
            set @sndPercent = COALESCE((@snd / @totalSolicitudes) * 100, 0);
            set @sdRanking = IF(@sdPercent = 100, 5, IF(@sdPercent >= 50, 3, IF(@sdPercent < 33, 0, 1)));
            set @sndRanking = IF(@snd = 0, 0, IF(@sndPercent >= 33, 5, IF(@sndPercent < 33 and @snd > 1, 3, 1)));

            /*Reserva Rápida, Reserva Inmediata*/
            set @rrRanking = IF(rr = 1, 5, 0);
            set @riRanking = IF(ri = 1, 5, 0);

            /*Frecuencia de actualización del calendario*/
            set @totalFAD = (select COUNT(*) FROM accommodation_calendar_frequency freq where MONTH(freq.updatedDate) = monthValue  and freq.accommodation = @accommodation);
            set @fadRanking = IF(@totalFAD = 0, 0, IF(@totalFAD > 0 AND @totalFAD < 4, 1, IF(@total >= 4 AND @total < 25, 3, 5)));

            /*Confianza*/
            set @totalRanking = COALESCE((select sum(COALESCE(rank.ranking, 0)) from ownership_ranking_extra rank where rank.accommodation = @accommodation and rank.endDate < @firstOfCurrentMonth order by rank.endDate DESC LIMIT 12), 0);
            set @confidenceRanking = IF(@totalRanking is null or @totalRanking < 1200, 0, IF(@totalRanking>=1200 and @totalRanking < 3840, 1, IF(@totalRanking >= 5520, 5, 3) ));

            IF @exists = 0 THEN
               INSERT INTO ownership_ranking_extra (accommodation,startDate,endDate,rankingPoints, awards, penalties, facturation,
               failureCasa, failureClients, newOffersReserved,positiveComments, negativeComments, reservations, sd,snd, rr, ri, fad, confidence)
               VALUES (@accommodation,@firstOfCurrentMonth,@lastOfCurrentMonth, @rankingPoints, @awards, @penaltiesRanking, @faRanking,
               @accommodationFailuresRanking, @touristFailuresRanking, @payedNewOffersRanking, @positiveRanking, @negativeRanking, @reservationsRanking, @sdRanking,
               @sndRanking, @rrRanking, @riRanking, @fadRanking, @confidenceRanking);
            ELSE
               UPDATE ownership_ranking_extra rank
               SET rank.penalties = @penaltiesRanking,
                   rank.awards = @awards,
                   rank.facturation = @faRanking,
                   rank.failureCasa = @accommodationFailuresRanking,
                   rank.failureClients = @touristFailuresRanking,
                   rank.newOffersReserved = @payedNewOffersRanking,
                   rank.positiveComments = @positiveRanking,
                   rank.negativeComments = @negativeRanking,
                   rank.reservations = @reservationsRanking,
                   rank.sd = @sdRanking,
                   rank.snd = @sndRanking,
                   rank.rr = @rrRanking,
                   rank.ri = @riRanking,
                   rank.fad = @fadRanking,
                   rank.confidence = @confidenceRanking
               WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;
            END IF;

            /*Calcular ranking - FINALLY!!*/
            UPDATE ownership_ranking_extra rank
            JOIN ranking_point p ON rank.rankingPoints = p.id
            SET rank.ranking = COALESCE(rank.awards * p.awards, 0) + COALESCE(rank.facturation * p.facturation, 0) + COALESCE(rank.fad * p.fad, 0) +
                                  COALESCE(rank.failureCasa * p.failureCasa, 0) + COALESCE(rank.failureClients * p.failureClients, 0) +
                                  COALESCE(rank.negativeComments * p.negativeComments, 0) + COALESCE(rank.newOffersReserved * p.newOffers, 0) +
                                  COALESCE(rank.penalties * p.penalties, 0) + COALESCE(rank.positiveComments * p.positiveComments, 0) +
                                  COALESCE(rank.reservations * p.reservations, 0) + COALESCE(rank.ri * p.ri, 0) + COALESCE(rank.rr * p.rr, 0) +
                                  COALESCE(rank.sd * p.sd, 0) + COALESCE(rank.snd * p.snd, 0) + COALESCE(rank.confidence * p.confidence, 0)
            WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;

            /*Colocar la categoria segun el ranking del mes*/
            set @ranking = (SELECT rank.ranking FROM ownership_ranking_extra rank WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth LIMIT 1);
            set @goldType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'rankingCategory' AND nom.nom_name = 'gold_ranking');
            set @silverType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'rankingCategory' AND nom.nom_name = 'silver_ranking');
            set @bronzeType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'rankingCategory' AND nom.nom_name = 'bronze_ranking');
            set @amateurType = (SELECT nom.nom_id FROM nomenclator nom WHERE nom.nom_category = 'rankingCategory' AND nom.nom_name = 'amateur_ranking');

            set @category = IF(@ranking < 50, @amateurType, IF(@ranking >= 50 and @ranking < 140, @bronzeType, IF(@ranking >= 140 and @ranking < 360, @silverType, @goldType )));

            UPDATE ownership_ranking_extra rank
            SET rank.category = @category
            WHERE rank.accommodation = @accommodation AND rank.startDate = @firstOfCurrentMonth AND rank.endDate = @lastOfCurrentMonth;

            UPDATE ownership o
            SET o.own_ranking = @ranking
            WHERE o.own_id = @accommodation;

          END LOOP;
          CLOSE accommodationsCursor;

        END;
        ");

    }
}
