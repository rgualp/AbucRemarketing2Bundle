﻿/*
Cantidad de pagos exitosos realizados por un cliente dividido por mes año
Author: Yanet Morales Ramirez
*/

select u.user_name, u.user_last_name, u.user_email, YEAR(p.created), MONTHNAME(p.created), COUNT(p.id)
from generalreservation gres
join user u on u.user_id =gres.gen_res_user_id
join ownershipreservation owres on owres.own_res_gen_res_id = gres.gen_res_id
join booking b on owres.own_res_reservation_booking = b.booking_id
join payment p on p.`booking_id` = b.booking_id
where gres.gen_res_status = 2 and p.status = 1
group by gres.gen_res_user_id, YEAR(p.created), MONTHNAME(p.created)
having COUNT(p.id) > 1
Order by u.user_email ASC, YEAR(p.created) DESC