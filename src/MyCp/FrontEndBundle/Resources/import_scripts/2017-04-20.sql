﻿/*
Author: Yanet Morales Ramirez
*/

/*Mostrar promedio de precios por temporadas y tipos de alojamiento*/
select o.own_type as Tipo,
AVG(r.room_price_down_to * (1 - o.own_commission_percent / 100)) as TemporadaBaja,
AVG(r.room_price_up_to * (1 - o.own_commission_percent / 100)) as TemporadaAlta
from room r
join ownership o on o.own_id = r.room_ownership
where o.own_type is not null and o.own_type != "" and o.own_status = 1
group by o.own_type;

/*Determinar el precio de habitacion que mas se reserva*/
select YEAR(gres.`gen_res_date`) as Year, MONTHNAME(gres.gen_res_date) as Month, ((owres.`own_res_total_in_site` * (1 - o.`own_commission_percent` / 100 )) / DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`)) as Precio, COUNT(owres.own_res_id) as totalReservas
from ownershipreservation owres
join generalreservation gres on gres.gen_res_id = owres.`own_res_gen_res_id`
join room r on r.room_id = owres.`own_res_selected_room_id`
join ownership o on o.own_id = r.`room_ownership`
where owres.own_res_status = 5
group by YEAR(gres.`gen_res_date`), MONTH(gres.gen_res_date), ((owres.`own_res_total_in_site` * (1 - o.`own_commission_percent` / 100 )) / DATEDIFF(owres.`own_res_reservation_to_date`, owres.`own_res_reservation_from_date`))
order by Year ASC, MONTH(gres.gen_res_date) ASC, totalReservas DESC