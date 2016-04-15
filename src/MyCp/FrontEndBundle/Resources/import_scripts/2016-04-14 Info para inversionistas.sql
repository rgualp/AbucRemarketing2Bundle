/*
Informacion para inversionistas
Author: Yanet Morales Ramirez
*/

/*1.	Clientes Turistas a.	Número de clientes */
select Anno, Mes, Dia, adultosReservadas + ninnosReservadas as turistasReservados, clientesReservadas,nochesReservadas,
                       adultosNoDisponible + ninnosNoDisponible as turistasNoDisponible,clientesNoDisponible,nochesNoDisponibles,
                       adultosDisponible + ninnosDisponible as turistasDisponible,clientesDisponible, nocheDisponibles,
                       adultos + ninnos as turistas, clientes, noches
from (
       select YEAR(genRes.gen_res_date) as Anno, MONTHNAME(genRes.gen_res_date) as Mes, Day(genRes.gen_res_date) as Dia,
              sum(owres.own_res_count_adults) as adultos,
              SUM(owres.own_res_count_childrens) as ninnos,
              COUNT(genRes.gen_res_user_id) as clientes,
              SUM(DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date)) as noches,
              sum(if(genRes.gen_res_status = 2, owres.own_res_count_adults, 0)) as adultosReservadas,
              SUM(if(genRes.gen_res_status = 2, owres.own_res_count_childrens, 0)) as ninnosReservadas,
              SUM(if(genRes.gen_res_status = 2, 1, 0)) as clientesReservadas,
              SUM(if(genRes.gen_res_status = 2, DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date) , 0)) as nochesReservadas,
              sum(if(genRes.gen_res_status = 3, owres.own_res_count_adults, 0)) as adultosNoDisponible,
              SUM(if(genRes.gen_res_status = 3, owres.own_res_count_childrens, 0)) as ninnosNoDisponible,
              SUM(if(genRes.gen_res_status = 3, 1, 0)) as clientesNoDisponible,
              SUM(if(genRes.gen_res_status = 3, DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date) , 0)) as nochesNoDisponibles,
              sum(if(genRes.gen_res_status = 1 or genRes.gen_res_status = 2 or genRes.gen_res_status = 8, owres.own_res_count_adults, 0)) as adultosDisponible,
              SUM(if(genRes.gen_res_status = 1 or genRes.gen_res_status = 2 or genRes.gen_res_status = 8, owres.own_res_count_childrens, 0)) as ninnosDisponible,
              SUM(if(genRes.gen_res_status = 1 or genRes.gen_res_status = 2 or genRes.gen_res_status = 8, 1, 0)) as clientesDisponible,
              SUM(if(genRes.gen_res_status = 1 or genRes.gen_res_status = 2 or genRes.gen_res_status = 8, DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date) , 0)) as nocheDisponibles
       from ownershipreservation owres
         join generalreservation genRes on genRes.gen_res_id = owres.own_res_gen_res_id
       GROUP BY Year(genRes.gen_res_date), Month(genRes.gen_res_date), Day(genRes.gen_res_date), genRes.gen_res_user_id
       ORDER BY Year(genRes.gen_res_date)) T;

select Anno, Mes, adults + kids as tourist, client from
  (select YEAR(creation_date) as Anno, MONTHNAME(creation_date) as Mes, sum(adults) as adults, sum(children) as kids, count(id) as client
   from old_reservation
   group by YEAR(creation_date), MONTH(creation_date)
   order by YEAR(creation_date)) T;

/*1.	Clientes Turistas b.	Facturación por cliente*/
select YEAR(p.created), MONTHNAME(p.created), DAY(p.created), count(distinct u.user_id) as clientes,
sum(DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`)) as noches,
FORMAT(sum(if(p.current_cuc_change_rate is not null, p.current_cuc_change_rate * p.payed_amount , cur.`curr_cuc_change` * p.payed_amount)), 2) as pagado
from payment p
  join currency cur on cur.curr_id = p.currency_id
  join booking b on p.booking_id = b.booking_id
  join ownershipreservation owres on owres.`own_res_reservation_booking` = b.booking_id
  join generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
  join user u on gres.gen_res_user_id = u.user_id
group by YEAR(p.created), Month(p.created), Day(p.created);

/*Destinos - Ingresos*/
SELECT year(created) as anno, monthname(created) as mes, day(created) as dia,
       format(SUM(IF(payment.current_cuc_change_rate IS NULL , payment.payed_amount*(SELECT currency.curr_cuc_change FROM mycasapa_prod.currency WHERE currency.curr_id=payment.currency_id),payment.current_cuc_change_rate*payment.payed_amount)), 2) as ingresos,
       destination.des_name as destino
from mycasapa_prod.payment INNER JOIN mycasapa_prod.booking ON payment.booking_id = booking.booking_id INNER JOIN mycasapa_prod.ownershipreservation ON booking.booking_id = ownershipreservation.own_res_reservation_booking INNER JOIN mycasapa_prod.generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN mycasapa_prod.ownership ON generalreservation.gen_res_own_id = ownership.own_id INNER JOIN mycasapa_prod.destination ON ownership.own_destination = destination.des_id
GROUP BY anno, mes, dia,destino
UNION
SELECT year(creation_date) as anno, monthname(creation_date) as mes, day(creation_date) as dia,
       format(SUM(old_payment.prepay_amount*(SELECT currency.curr_cuc_change FROM mycasapa_prod.currency WHERE currency.curr_code=old_payment.currency_code)), 2) as ingresos,
       destination.des_name as destino
from mycasapa_site.old_payment INNER JOIN mycasapa_prod.ownership ON own_mcp_code=accommodation_code INNER JOIN mycasapa_prod.destination ON ownership.own_destination = destination.des_id
GROUP BY anno, mes, dia, destino
ORDER BY anno ASC, mes ASC, destino ASC

/*Facturacion por paises*/
SELECT year(created) as anno, monthname(created) as mes,day(created) as dia,
       format(SUM(IF(payment.current_cuc_change_rate IS NULL , payment.payed_amount*(SELECT currency.curr_cuc_change FROM mycasapa_prod.currency WHERE currency.curr_id=payment.currency_id),payment.current_cuc_change_rate*payment.payed_amount)),2) as ingresos,
       country.co_name as pais
from mycasapa_prod.payment INNER JOIN mycasapa_prod.booking ON payment.booking_id = booking.booking_id INNER JOIN mycasapa_prod.user ON booking_user_id=user_id INNER JOIN mycasapa_prod.country ON user.user_country = country.co_id
GROUP BY anno, mes, dia,pais
UNION
SELECT year(creation_date) as anno, monthname(creation_date) as mes,day(creation_date) as dia,
       format(SUM(old_payment.prepay_amount*(SELECT currency.curr_cuc_change FROM mycasapa_prod.currency WHERE currency.curr_code=old_payment.currency_code)),2) as ingresos,
       old_payment.tourist_country as pais
from old_payment
GROUP BY anno, mes, dia, pais
ORDER BY anno ASC, mes ASC, dia ASC,ingresos DESC

select YEAR(res.`creation_date`) as Anno, MONTHNAME(res.`creation_date`) as Mes,DAY(res.`creation_date`) as Dia,
       SUM(res.adults+res.children) as turistas,
       COUNT(distinct res.`tourist_email`) as clientesSPagos,
       SUM(res.nights)  as nochesPagadas
from old_payment res
group by YEAR(res.`creation_date`), MONTH(res.`creation_date`), DAY(res.`creation_date`)

SELECT year(generalreservation.gen_res_date) as anno, monthname(generalreservation.gen_res_date) as mes,
       destination.des_name as destino,
       (SELECT COUNT(DISTINCT(generalreservation.gen_res_user_id)) FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id  WHERE year(gen_res_date)=anno AND  monthname(gen_res_date)=mes AND ownership.own_destination=destination.des_id) AS clientes,
       count(ownershipreservation.own_res_id) AS solicitudes,
       (SELECT COUNT(ownershipreservation.own_res_id) FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id  INNER JOIN booking ON ownershipreservation.own_res_reservation_booking = booking.booking_id INNER JOIN payment ON booking.booking_id = payment.booking_id WHERE year(gen_res_date)=anno AND  monthname(gen_res_date)=mes  AND ownership.own_destination=destination.des_id) AS reservadas,
       (SELECT SUM(DATEDIFF(ownershipreservation.own_res_reservation_to_date, ownershipreservation.own_res_reservation_from_date)) FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id  INNER JOIN booking ON ownershipreservation.own_res_reservation_booking = booking.booking_id INNER JOIN payment ON booking.booking_id = payment.booking_id WHERE year(gen_res_date)=anno AND  monthname(gen_res_date)=mes  AND ownership.own_destination=destination.des_id) AS noches_reservadas,
       (SELECT COUNT(ownershipreservation.own_res_id) FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id  WHERE year(gen_res_date)=anno AND  monthname(gen_res_date)=mes AND generalreservation.gen_res_status=1 AND ownership.own_destination=destination.des_id) AS disponibles,
       (SELECT SUM(DATEDIFF(ownershipreservation.own_res_reservation_to_date, ownershipreservation.own_res_reservation_from_date)) FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id  WHERE year(gen_res_date)=anno AND  monthname(gen_res_date)=mes AND generalreservation.gen_res_status=1 AND ownership.own_destination=destination.des_id) AS noches_disponibles,
       (SELECT COUNT(ownershipreservation.own_res_id) FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id WHERE year(gen_res_date)=anno AND  monthname(gen_res_date)=mes AND generalreservation.gen_res_status=3 AND ownership.own_destination=destination.des_id) AS no_disponibles,
       (SELECT SUM(DATEDIFF(ownershipreservation.own_res_reservation_to_date, ownershipreservation.own_res_reservation_from_date)) FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id WHERE year(gen_res_date)=anno AND  monthname(gen_res_date)=mes AND generalreservation.gen_res_status=3 AND ownership.own_destination=destination.des_id) AS noches_no_disponibles
from ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id INNER JOIN destination ON ownership.own_destination = destination.des_id
GROUP BY anno, mes, destino
UNION
SELECT year(old_reservation.creation_date) as anno, monthname(old_reservation.creation_date) as mes,
       destination.des_name as destino,
       (SELECT COUNT(DISTINCT(old_reservation.tourist_email)) FROM old_reservation INNER JOIN ownership ON old_reservation.accommodation_code = ownership.own_mcp_code  WHERE year(creation_date)=anno AND  monthname(creation_date)=mes AND ownership.own_destination=destination.des_id) AS clientes,
       count(old_reservation.id) as solicitudes,
       (SELECT COUNT(old_reservation.id) FROM old_reservation INNER JOIN old_payment ON old_reservation.reservation_code=old_payment.reservation_code INNER JOIN ownership ON old_reservation.accommodation_code = ownership.own_mcp_code WHERE year(old_reservation.creation_date)=anno AND  monthname(old_reservation.creation_date)=mes  AND ownership.own_destination=destination.des_id) AS reservadas,
       (SELECT SUM(old_reservation.nights) FROM old_reservation INNER JOIN old_payment ON old_reservation.reservation_code=old_payment.reservation_code INNER JOIN ownership ON old_reservation.accommodation_code = ownership.own_mcp_code WHERE year(old_reservation.creation_date)=anno AND  monthname(old_reservation.creation_date)=mes  AND ownership.own_destination=destination.des_id) AS noches_reservadas,
       (SELECT COUNT(old_reservation.id) FROM old_reservation INNER JOIN old_payment ON old_reservation.reservation_code=old_payment.reservation_code INNER JOIN ownership ON old_reservation.accommodation_code = ownership.own_mcp_code WHERE year(old_reservation.creation_date)=anno AND  monthname(old_reservation.creation_date)=mes  AND ownership.own_destination=destination.des_id) AS disponibles,
       (SELECT SUM(old_reservation.nights) FROM old_reservation INNER JOIN old_payment ON old_reservation.reservation_code=old_payment.reservation_code INNER JOIN ownership ON old_reservation.accommodation_code = ownership.own_mcp_code WHERE year(old_reservation.creation_date)=anno AND  monthname(old_reservation.creation_date)=mes  AND ownership.own_destination=destination.des_id) AS noches_disponibles,
       NULL AS no_disponibles,
       NULL AS noches_no_disponibles
FROM old_reservation INNER JOIN ownership ON old_reservation.accommodation_code=ownership.own_mcp_code INNER JOIN destination ON ownership.own_destination = destination.des_id
GROUP BY anno, mes, destino
ORDER BY anno ASC , mes ASC , destino ASC
;

/*Total de casas con reservas pagadas, por año*/
select anno, count(distinct Code) from
  (select YEAR(gres.gen_res_date) as anno, own.own_mcp_code as Code
   from generalreservation gres
     join ownership own  on own.own_id = gres.gen_res_own_id
     join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
     join booking b on b.booking_id = owres.own_res_reservation_booking
     join payment p on p.`booking_id` = b.`booking_id`
   where gres.gen_res_status = 2
   union
   select YEAR(pay.creation_date) as anno, pay.accommodation_code as Code
   from old_payment pay) T
group by anno
order by anno;