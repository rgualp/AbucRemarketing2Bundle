﻿/*
Clients with just one destination in season
Author: Yanet Morales Ramirez
*/

select T.Cliente, T.Pais, T.Alojamiento, T.Destino, T.Habitaciones, T.Huespedes, T.Pagado as PagadoCUC, T.Desde, T.Hasta from (
select u.user_id,CONCAT(u.user_user_name,' ', u.user_last_name) as Cliente, co.co_name as Pais, own.own_mcp_code as Alojamiento, d.des_name as Destino,
count(owr.own_res_gen_res_id) as Habitaciones,
sum(owr.own_res_count_adults + owr.own_res_count_childrens) as Huespedes,
pay.payed_amount * cur.curr_cuc_change as Pagado,
owr.own_res_reservation_from_date as Desde, owr.own_res_reservation_to_date as Hasta
from ownershipreservation owr
join generalreservation gres on owr.own_res_gen_res_id = gres.gen_res_id
join user u on u.user_id = gres.gen_res_user_id
join ownership own on own.own_id = gres.gen_res_own_id
join country co on co.co_id = u.user_country
join destination d on d.des_id = own.own_destination
join booking b on b.booking_id = owr.own_res_reservation_booking
join payment pay on pay.booking_id = b.booking_id
join currency cur on cur.curr_id = pay.currency_id
where owr.own_res_status = 5
and own_res_reservation_to_date > '2014-11-01' and own_res_reservation_to_date <= '2015-04-30'
group by owr.own_res_gen_res_id) T
group by T.user_id
having count(T.user_id) = 1
order by T.Desde ASC