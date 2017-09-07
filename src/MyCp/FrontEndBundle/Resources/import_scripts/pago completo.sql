﻿/*
Pago completo
Author: Yanet Morales Ramirez
*/

select YEAR(T.date), MONTH(T.date), SUM(T.price)
from
(select DATE_ADD(owres.`own_res_reservation_to_date`, INTERVAL 10 DAY) as date, owres.`own_res_total_in_site` * (1 - o.`own_commission_percent`/100) as price
from ownershipreservation owres
join generalreservation gres on owres.own_res_gen_res_id = gres.`gen_res_id`
join ownership o on o.own_id = gres.`gen_res_own_id`
where owres.own_res_status = 5) T
where YEAR(T.date) >= 2017
group by YEAR(T.date), MONTH(T.date)