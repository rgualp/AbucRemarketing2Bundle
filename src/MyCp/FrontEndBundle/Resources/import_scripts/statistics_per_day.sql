/*
Statistics per date range
Author: Yanet Morales Ramirez
*/

/*
Pagos diarios en un rango de fechas
*/
select DATE_FORMAT(p.modified, "%Y-%m-%d") as Date, count(p.id) as Total
from payment p
where p.modified >= '2016-01-01'
      and p.modified <= '2016-03-10'
      and (p.status = 1 or p.status = 4)
group by DATE_FORMAT(p.modified, "%Y-%m-%d")
order by p.modified;

/*
Habitaciones solicitadas-pagadas por dia
*/
select *,  ROUND(Reserved_Rooms * 100 / Total_Rooms) as Percent
from (select gs.`gen_res_status_date` as Date, count(owres.own_res_id) as Total_Rooms,
SUM(if(owres.own_res_status = 5, 1, 0)) AS Reserved_Rooms
from ownershipreservation owres
join generalreservation gs on owres.`own_res_gen_res_id` = gs.gen_res_id
where gs.gen_res_status_date >= '2016-01-01'
and gs.`gen_res_status_date` <= '2016-03-10'
group by gs.`gen_res_status_date`
order by gs.`gen_res_status_date`) T;

/*
Alojamientos solicitados-pagados por dia
*/
select *,  ROUND(Reserved * 100 / Total) as Percent
from (select gs.`gen_res_status_date` as Date, count(gs.gen_res_id) as Total,
SUM(if(gs.gen_res_status = 2, 1, 0)) AS Reserved
from generalreservation gs
where gs.gen_res_status_date >= '2016-01-01'
and gs.`gen_res_status_date` <= '2016-03-10'
group by gs.`gen_res_status_date`
order by gs.`gen_res_status_date`) T;