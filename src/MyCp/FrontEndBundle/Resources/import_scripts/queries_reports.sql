/*
Some reports queries
Author: Yanet Morales Ramirez
*/

/*
Comparing receiving reservations in a selected period. Comparing current year statistics with previous year
*/
select gres.`gen_res_date`, sum(IF(gres.`gen_res_date` >= '2014-10-01' and gres.`gen_res_date` <= '2014-10-31', 1, 0)) as Total_2014
  , sum(IF(gres.`gen_res_date` >= '2015-10-01' and gres.`gen_res_date` <= '2015-10-31', 1, 0)) as Total_2015
from generalreservation gres
where gres.`gen_res_date` >= '2014-10-01' and gres.`gen_res_date` <= '2014-10-31'
      or gres.`gen_res_date` >= '2015-10-01' and gres.`gen_res_date` <= '2015-10-31'
group by gres.`gen_res_date`;

/*Comparing reservations by status. Comparing 2014 and 2015*/
select gres.`gen_res_status`
  , sum(IF(gres.`gen_res_date` >= '2014-10-01' and gres.`gen_res_date` <= '2014-10-31', 1, 0)) as Total_2014
  , sum(IF(gres.`gen_res_date` >= '2015-10-01' and gres.`gen_res_date` <= '2015-10-31', 1, 0)) as Total_2015
from generalreservation gres
where gres.`gen_res_date` >= '2014-10-01' and gres.`gen_res_date` <= '2014-10-31'
      or gres.`gen_res_date` >= '2015-10-01' and gres.`gen_res_date` <= '2015-10-31'
group by gres.`gen_res_status`;

/*Selecting by date by status*/
SELECT gres.`gen_res_date`,
  SUM(CASE gres.`gen_res_status` WHEN 0 THEN 1 ELSE 0 END) Pendientes,
  SUM(CASE gres.`gen_res_status` WHEN 1 THEN 1 ELSE 0 END) Disponibles,
  SUM(CASE gres.`gen_res_status` WHEN 2 THEN 1 ELSE 0 END) Reservadas,
  SUM(CASE gres.`gen_res_status` WHEN 3 THEN 1 ELSE 0 END) No_Disponibles,
  SUM(CASE gres.`gen_res_status` WHEN 6 THEN 1 ELSE 0 END) Canceladas,
  SUM(CASE gres.`gen_res_status` WHEN 8 THEN 1 ELSE 0 END) Vencidas
FROM generalreservation gres
where gres.`gen_res_date` >= '2014-10-01' and gres.`gen_res_date` <= '2014-10-31'
GROUP BY gres.`gen_res_date`
ORDER BY gres.`gen_res_date`;

/*Selecting by countries. For a selected period*/
SELECT c.co_name,
  SUM(CASE gres.`gen_res_status` WHEN 0 THEN 1 ELSE 0 END) Pendientes,
  SUM(CASE gres.`gen_res_status` WHEN 1 THEN 1 ELSE 0 END) Disponibles,
  SUM(CASE gres.`gen_res_status` WHEN 2 THEN 1 ELSE 0 END) Reservadas,
  SUM(CASE gres.`gen_res_status` WHEN 3 THEN 1 ELSE 0 END) No_Disponibles,
  SUM(CASE gres.`gen_res_status` WHEN 6 THEN 1 ELSE 0 END) Canceladas,
  SUM(CASE gres.`gen_res_status` WHEN 8 THEN 1 ELSE 0 END) Vencidas,
  count(gres.`gen_res_id`) as TOTAL
FROM generalreservation gres
  join user u on gres.`gen_res_user_id` = u.user_id
  join country c on u.user_country = c.co_id
where gres.`gen_res_date` >= '2014-10-01' and gres.`gen_res_date` <= '2014-10-31'
GROUP BY c.co_id
ORDER BY TOTAL DESC;

/*By countries. Comparing 2 or more years*/
select T.co_name, Total_2014, Total_2015, (Total_2014 + Total_2015) as TOTAL
from
  (SELECT c.co_name,
     SUM(if(gres.`gen_res_date` >= '2014-01-01' and gres.`gen_res_date` <= '2014-12-31', 1, 0)) Total_2014,
     SUM(if(gres.`gen_res_date` >= '2015-01-01' and gres.`gen_res_date` <= '2015-12-31', 1, 0)) Total_2015
   FROM generalreservation gres
     join user u on gres.`gen_res_user_id` = u.user_id
     join country c on u.user_country = c.co_id
   GROUP BY c.co_id) T
ORDER BY TOTAL DESC;

