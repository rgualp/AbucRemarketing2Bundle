﻿/*
Estadisticas por habitaciones reservadas
Author: Yanet Morales Ramirez
*/

/*Estadisticas cantidad de habitaciones reservadas por precio, noches y año*/

select TRUNCATE((owres.`own_res_total_in_site` / DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`)), 2) as roomPrice,
SUM(CASE WHEN (YEAR(gres.gen_res_date)=2015 AND DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`) = 1) THEN 1 ELSE 0 END) AS '2015 - 1 noche',
SUM(CASE WHEN (YEAR(gres.gen_res_date)=2016 AND DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`) = 1) THEN 1 ELSE 0 END) AS '2016 - 1 noche',
SUM(CASE WHEN (YEAR(gres.gen_res_date)=2015 AND DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`) = 2) THEN 1 ELSE 0 END) AS '2015 - 2 noches',
SUM(CASE WHEN (YEAR(gres.gen_res_date)=2016 AND DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`) = 2) THEN 1 ELSE 0 END) AS '2016 - 2 noches',
SUM(CASE WHEN (YEAR(gres.gen_res_date)=2015 AND DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`) = 3) THEN 1 ELSE 0 END) AS '2015 - 3 noches',
SUM(CASE WHEN (YEAR(gres.gen_res_date)=2016 AND DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`) = 3) THEN 1 ELSE 0 END) AS '2016 - 3 noches',
SUM(CASE WHEN (YEAR(gres.gen_res_date)=2015 AND DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`) = 4) THEN 1 ELSE 0 END) AS '2015 - 4 noches',
SUM(CASE WHEN (YEAR(gres.gen_res_date)=2016 AND DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`) = 4) THEN 1 ELSE 0 END) AS '2016 - 4 noches',
SUM(CASE WHEN (YEAR(gres.gen_res_date)=2015 AND DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`) = 5) THEN 1 ELSE 0 END) AS '2015 - 5 noches',
SUM(CASE WHEN (YEAR(gres.gen_res_date)=2016 AND DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`) = 5) THEN 1 ELSE 0 END) AS '2016 - 5 noches',
SUM(CASE WHEN (YEAR(gres.gen_res_date)=2015 AND DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`) > 5) THEN 1 ELSE 0 END) AS '2015 - +5 noches',
SUM(CASE WHEN (YEAR(gres.gen_res_date)=2016 AND DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`) > 5) THEN 1 ELSE 0 END) AS '2016 - +5 noches'
from generalreservation gres
join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
join booking b on b.`booking_id` = owres.`own_res_reservation_booking`
join payment p on p.`booking_id` = b.`booking_id`
where gres.gen_res_status = 2
and (p.status = 1 or p.status = 4)
and (owres.`own_res_total_in_site` / DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`)) is not null
AND MONTH(gres.gen_res_date) = 10
GROUP BY roomPrice

/**/
select DAY(gres.gen_res_date) as Dia,
SUM(IF(o.`own_inmediate_booking` = 1, 1, 0)) as RR,
TRUNCATE(SUM(IF(o.`own_inmediate_booking` = 1, (p.`payed_amount` / p.`current_cuc_change_rate`), 0)),2) as Facturacion_RR,
SUM(IF(o.`own_inmediate_booking_2` = 1, 1, 0)) as RI,
TRUNCATE(SUM(IF(o.`own_inmediate_booking_2` = 1, (p.`payed_amount` / p.`current_cuc_change_rate`), 0)), 2) as Facturacion_RI
from generalreservation gres
join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
join booking b on b.`booking_id` = owres.`own_res_reservation_booking`
join payment p on p.`booking_id` = b.`booking_id`
join ownership o on gres.gen_res_own_id = o.own_id
where gres.gen_res_status = 2
and (p.status = 1 or p.status = 4)
and gres.gen_res_date >= '2016-11-01'
and gres.gen_res_date <= '2016-11-30'
GROUP BY Dia