﻿/*
Author: Yanet Morales Ramirez
*/

/*Changing outdated reservations with payment*/
UPDATE ownershipreservation owres
join booking b on b.booking_id = owres.`own_res_reservation_booking`
join payment p on p.booking_id = b.booking_id
SET owres.own_res_status = 5
where owres.own_res_status = 6
and (p.status = 1 or p.status = 4);

UPDATE generalreservation gres
join ownershipreservation owres on gres.gen_res_id = owres.own_res_gen_res_id
join booking b on b.booking_id = owres.`own_res_reservation_booking`
join payment p on p.booking_id = b.booking_id
SET gres.gen_res_status = 2
where gres.gen_res_status = 8
and owres.own_res_status = 5
and (p.status = 1 or p.status = 4);

/*100 casas mas viejas con mas reservas para promocion fin de año*/
select DISTINCT T.own_mcp_code, T.owner, T.email from
(select o.own_id, o.own_mcp_code, IF(o.own_email_1 IS NOT NULL and o.own_email_1 != '', o.own_email_1, o.own_email_2) as email,
IF(o.own_homeowner_1 IS NOT NULL AND o.own_homeowner_1 != '', o.own_homeowner_1, o.own_homeowner_2) as owner, o.own_name, o.own_ranking,
(SELECT COUNT(*) FROM ownershipreservation owr JOIN generalreservation gres on owr.own_res_gen_res_id = gres.gen_res_id
JOIN booking b on b.booking_id = owr.own_res_reservation_booking WHERE owr.own_res_status = 5 AND gres.gen_res_own_id = o.own_id) as totalReservations
from ownership o
where o.own_status = 1
AND ((o.own_email_1 IS NOT NULL and o.own_email_1 != '') OR (o.own_email_2 IS NOT NULL and o.own_email_2 != ''))) T
order by T.totalReservations DESC, T.own_ranking DESC, T.own_id ASC
LIMIT 100
into outfile 'data.xls';
