/*
Reportes - Reservas
Author: Yanet Morales Ramirez
*/
/*General por año, mes*/
select YEAR(genRes.gen_res_date) as Anno, MONTH(genRes.gen_res_date) as Mes, COUNT(*) as TOTAL,
       SUM(if(owres.own_res_status = 0, 1, 0)) AS Pendientes,
       SUM(if(owres.own_res_status = 1 or owres.own_res_status = 2, 1, 0)) AS Disponibles,
       SUM(if(owres.own_res_status = 3, 1, 0)) AS 'No Disponibles',
       SUM(if(owres.own_res_status = 4, 1, 0)) AS Canceladas,
       SUM(if(owres.own_res_status = 5, 1, 0)) AS Reservadas,
       SUM(if(owres.own_res_status = 6, 1, 0)) AS Vencidas
from ownershipreservation owres
  join generalreservation genRes on genRes.gen_res_id = owres.own_res_gen_res_id
GROUP BY Year(genRes.gen_res_date), Month(genRes.gen_res_date)
ORDER BY Year(genRes.gen_res_date), Month(genRes.gen_res_date);

/*General por año, mes, casas*/
select YEAR(genRes.gen_res_date) as Anno, MONTH(genRes.gen_res_date) as Mes, o.own_mcp_code, COUNT(*) as TOTAL,
       SUM(if(owres.own_res_status = 0, 1, 0)) AS Pendientes,
       SUM(if(owres.own_res_status = 1 or owres.own_res_status = 2, 1, 0)) AS Disponibles,
       SUM(if(owres.own_res_status = 3, 1, 0)) AS 'No Disponibles',
       SUM(if(owres.own_res_status = 4, 1, 0)) AS Canceladas,
       SUM(if(owres.own_res_status = 5, 1, 0)) AS Reservadas,
       SUM(if(owres.own_res_status = 6, 1, 0)) AS Vencidas
from ownershipreservation owres
  join generalreservation genRes on genRes.gen_res_id = owres.own_res_gen_res_id
  join ownership o on o.own_id = genRes.`gen_res_own_id`
GROUP BY Year(genRes.gen_res_date), Month(genRes.gen_res_date), genRes.`gen_res_own_id`
ORDER BY Year(genRes.gen_res_date), Month(genRes.gen_res_date), TOTAL DESC;

/*General por año, casas*/
select YEAR(genRes.gen_res_date) as Anno, o.own_mcp_code, COUNT(*) as TOTAL,
       SUM(if(owres.own_res_status = 0, 1, 0)) AS Pendientes,
       SUM(if(owres.own_res_status = 1 or owres.own_res_status = 2, 1, 0)) AS Disponibles,
       SUM(if(owres.own_res_status = 3, 1, 0)) AS 'No Disponibles',
       SUM(if(owres.own_res_status = 4, 1, 0)) AS Canceladas,
       SUM(if(owres.own_res_status = 5, 1, 0)) AS Reservadas,
       SUM(if(owres.own_res_status = 6, 1, 0)) AS Vencidas
from ownershipreservation owres
  join generalreservation genRes on genRes.gen_res_id = owres.own_res_gen_res_id
  join ownership o on o.own_id = genRes.`gen_res_own_id`
GROUP BY Year(genRes.gen_res_date), genRes.`gen_res_own_id`
ORDER BY Year(genRes.gen_res_date), TOTAL DESC;

/*General por año, mes, provincia*/
select YEAR(genRes.gen_res_date) as Anno, MONTH(genRes.gen_res_date) as Mes, p.`prov_name`, COUNT(*) as TOTAL,
       SUM(if(owres.own_res_status = 0, 1, 0)) AS Pendientes,
       SUM(if(owres.own_res_status = 1 or owres.own_res_status = 2, 1, 0)) AS Disponibles,
       SUM(if(owres.own_res_status = 3, 1, 0)) AS 'No Disponibles',
       SUM(if(owres.own_res_status = 4, 1, 0)) AS Canceladas,
       SUM(if(owres.own_res_status = 5, 1, 0)) AS Reservadas,
       SUM(if(owres.own_res_status = 6, 1, 0)) AS Vencidas
from ownershipreservation owres
  join generalreservation genRes on genRes.gen_res_id = owres.own_res_gen_res_id
  join ownership o on o.own_id = genRes.`gen_res_own_id`
  join province p on o.`own_address_province` = p.prov_id
GROUP BY Year(genRes.gen_res_date), Month(genRes.gen_res_date), o.`own_address_province`
ORDER BY Year(genRes.gen_res_date), Month(genRes.gen_res_date), TOTAL DESC;

/*General por año, provincia*/
select YEAR(genRes.gen_res_date) as Anno, p.`prov_name`, COUNT(*) as TOTAL,
       SUM(if(owres.own_res_status = 0, 1, 0)) AS Pendientes,
       SUM(if(owres.own_res_status = 1 or owres.own_res_status = 2, 1, 0)) AS Disponibles,
       SUM(if(owres.own_res_status = 3, 1, 0)) AS 'No Disponibles',
       SUM(if(owres.own_res_status = 4, 1, 0)) AS Canceladas,
       SUM(if(owres.own_res_status = 5, 1, 0)) AS Reservadas,
       SUM(if(owres.own_res_status = 6, 1, 0)) AS Vencidas
from ownershipreservation owres
  join generalreservation genRes on genRes.gen_res_id = owres.own_res_gen_res_id
  join ownership o on o.own_id = genRes.`gen_res_own_id`
  join province p on o.`own_address_province` = p.prov_id
GROUP BY Year(genRes.gen_res_date), o.`own_address_province`
ORDER BY Year(genRes.gen_res_date), TOTAL DESC;

/*General por año, mes, municipio*/
select YEAR(genRes.gen_res_date) as Anno, MONTH(genRes.gen_res_date) as Mes, m.`mun_name`, COUNT(*) as TOTAL,
       SUM(if(owres.own_res_status = 0, 1, 0)) AS Pendientes,
       SUM(if(owres.own_res_status = 1 or owres.own_res_status = 2, 1, 0)) AS Disponibles,
       SUM(if(owres.own_res_status = 3, 1, 0)) AS 'No Disponibles',
       SUM(if(owres.own_res_status = 4, 1, 0)) AS Canceladas,
       SUM(if(owres.own_res_status = 5, 1, 0)) AS Reservadas,
       SUM(if(owres.own_res_status = 6, 1, 0)) AS Vencidas
from ownershipreservation owres
  join generalreservation genRes on genRes.gen_res_id = owres.own_res_gen_res_id
  join ownership o on o.own_id = genRes.`gen_res_own_id`
  join `municipality` m on o.`own_address_municipality` = m.mun_id
GROUP BY Year(genRes.gen_res_date), Month(genRes.gen_res_date), o.`own_address_municipality`
ORDER BY Year(genRes.gen_res_date), Month(genRes.gen_res_date), TOTAL DESC;

/*General por año, mes, destino*/
select YEAR(genRes.gen_res_date) as Anno, MONTH(genRes.gen_res_date) as Mes, d.`des_name`, COUNT(*) as TOTAL,
       SUM(if(owres.own_res_status = 0, 1, 0)) AS Pendientes,
       SUM(if(owres.own_res_status = 1 or owres.own_res_status = 2, 1, 0)) AS Disponibles,
       SUM(if(owres.own_res_status = 3, 1, 0)) AS 'No Disponibles',
       SUM(if(owres.own_res_status = 4, 1, 0)) AS Canceladas,
       SUM(if(owres.own_res_status = 5, 1, 0)) AS Reservadas,
       SUM(if(owres.own_res_status = 6, 1, 0)) AS Vencidas
from ownershipreservation owres
  join generalreservation genRes on genRes.gen_res_id = owres.own_res_gen_res_id
  join ownership o on o.own_id = genRes.`gen_res_own_id`
  join `destination` d on o.`own_destination` = d.des_id
GROUP BY Year(genRes.gen_res_date), Month(genRes.gen_res_date), o.`own_destination`
ORDER BY Year(genRes.gen_res_date), Month(genRes.gen_res_date), TOTAL DESC;