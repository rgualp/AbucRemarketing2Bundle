﻿/*
Getting tourist itinerary (one reservation before and one reservation after)
Author: Yanet Morales Ramirez
*/


set @accommodationId = 10;
set @userId = 23595;
set @confirmFromDate = "2016-08-05";
set @confirmToDate = "2016-08-07";

select o.own_mcp_code, owres.own_res_reservation_booking, owres.`own_res_reservation_from_date` as fromDate, owres.`own_res_reservation_to_date` as toDate
from ownershipreservation owres
join generalreservation gres on owres.own_res_gen_res_id = gres.gen_res_id
join user u on u.user_id = gres.gen_res_user_id
join ownership o on o.own_id = gres.gen_res_own_id
join booking b on b.booking_id = owres.own_res_reservation_booking
join payment p on p.`booking_id` = b.`booking_id`
where gres.gen_res_status = 2
and (p.status = 1 or p.status = 4)
and u.user_id = @userId
and gres.gen_res_own_id != @accommodationId
and (owres.`own_res_reservation_to_date` >= DATE_SUB(@confirmFromDate, INTERVAL 14 day) and owres.`own_res_reservation_to_date` <= @confirmFromDate)
order by owres.`own_res_reservation_from_date` DESC
limit 1
;


select o.own_id, o.own_mcp_code, owres.`own_res_reservation_from_date` as fromDate, owres.`own_res_reservation_to_date` as toDate
from ownershipreservation owres
join generalreservation gres on owres.own_res_gen_res_id = gres.gen_res_id
join user u on u.user_id = gres.gen_res_user_id
join ownership o on o.own_id = gres.gen_res_own_id
join booking b on b.booking_id = owres.own_res_reservation_booking
join payment p on p.`booking_id` = b.`booking_id`
where gres.gen_res_status = 2
and (p.status = 1 or p.status = 4)
and u.user_id = @userId
and gres.gen_res_own_id != @accommodationId
and (owres.`own_res_reservation_from_date` >= @confirmToDate and owres.`own_res_reservation_from_date` <= DATE_ADD(@confirmToDate, INTERVAL 14 day))
order by owres.`own_res_reservation_from_date` ASC
limit 1
;