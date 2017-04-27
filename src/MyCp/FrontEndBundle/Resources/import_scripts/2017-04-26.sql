﻿/*
Author: Yanet Morales Ramirez
*/

/*Cantidad de reservas por tipo de alojamiento*/
select YEAR(gres.`gen_res_date`) as Year, MONTHNAME(gres.gen_res_date) as Month, o.own_type as Tipo, COUNT(owres.own_res_id) as totalReservas
from ownershipreservation owres
join generalreservation gres on gres.gen_res_id = owres.`own_res_gen_res_id`
join room r on r.room_id = owres.`own_res_selected_room_id`
join ownership o on o.own_id = r.`room_ownership`
where owres.own_res_status = 5 and YEAR(gres.`gen_res_date`) >= 2016
group by YEAR(gres.`gen_res_date`), MONTH(gres.gen_res_date), o.own_type
order by Year ASC, MONTH(gres.gen_res_date) ASC, totalReservas DESC;

/*Cantidad de personas por reservas*/
select YEAR(gres.`gen_res_date`) as Year, MONTHNAME(gres.gen_res_date) as Month, (owres.`own_res_count_adults` + owres.`own_res_count_childrens` + 1) as Personas, COUNT(owres.own_res_id) as totalReservas
from ownershipreservation owres
join generalreservation gres on gres.gen_res_id = owres.`own_res_gen_res_id`
join room r on r.room_id = owres.`own_res_selected_room_id`
join ownership o on o.own_id = r.`room_ownership`
where owres.own_res_status = 5 and YEAR(gres.`gen_res_date`) >= 2016
group by YEAR(gres.`gen_res_date`), MONTH(gres.gen_res_date), (owres.`own_res_count_adults` + owres.`own_res_count_childrens` + 1)
order by Year ASC, MONTH(gres.gen_res_date) ASC, totalReservas DESC;

/*REservas con niños*/
select YEAR(gres.`gen_res_date`) as Year, MONTHNAME(gres.gen_res_date) as Month,
(owres.`own_res_count_adults` + owres.`own_res_count_childrens` + 1) as Personas,
COUNT(owres.own_res_id) as totalReservas
from ownershipreservation owres
join generalreservation gres on gres.gen_res_id = owres.`own_res_gen_res_id`
join room r on r.room_id = owres.`own_res_selected_room_id`
join ownership o on o.own_id = r.`room_ownership`
where owres.own_res_status = 5 and YEAR(gres.`gen_res_date`) >= 2016 and owres.`own_res_count_childrens` > 0
group by YEAR(gres.`gen_res_date`), MONTH(gres.gen_res_date), (owres.`own_res_count_adults` + owres.`own_res_count_childrens`  + 1)
order by Year ASC, MONTH(gres.gen_res_date) ASC, totalReservas DESC

/*Niños, personas, promedio por meses y año*/
select T.*, (T.ConNiños * 100) / T.totalReservas as promedio
from
(select YEAR(gres.`gen_res_date`) as Year, MONTHNAME(gres.gen_res_date) as Month,
(owres.`own_res_count_adults` + owres.`own_res_count_childrens` + 1) as Personas,
SUM(IF(owres.`own_res_count_childrens` > 0, 1, 0)) as ConNiños,
COUNT(owres.own_res_id) as totalReservas
from ownershipreservation owres
join generalreservation gres on gres.gen_res_id = owres.`own_res_gen_res_id`
join room r on r.room_id = owres.`own_res_selected_room_id`
join ownership o on o.own_id = r.`room_ownership`
where owres.own_res_status = 5 and gres.`gen_res_date` >= '2016-10-01'
group by YEAR(gres.`gen_res_date`), MONTH(gres.gen_res_date), (owres.`own_res_count_adults` + owres.`own_res_count_childrens`  + 1)
order by Year ASC, MONTH(gres.gen_res_date) ASC, totalReservas DESC) T

/*Niños, personas, promedio general*/
select T.*, (T.ConNiños * 100) / T.totalReservas as promedio
from
( SELECT
(owres.`own_res_count_adults` + owres.`own_res_count_childrens` + 1) as Personas,
SUM(IF(owres.`own_res_count_childrens` > 0, 1, 0)) as ConNiños,
COUNT(owres.own_res_id) as totalReservas
from ownershipreservation owres
join generalreservation gres on gres.gen_res_id = owres.`own_res_gen_res_id`
join room r on r.room_id = owres.`own_res_selected_room_id`
join ownership o on o.own_id = r.`room_ownership`
where owres.own_res_status = 5 and gres.`gen_res_date` >= '2016-10-01'
group by (owres.`own_res_count_adults` + owres.`own_res_count_childrens`  + 1)
) T