/*
Queries for Excel File (bookings info)
Author: Yanet Morales Ramirez
*/

/*List of bookings and payments totals*/
select T.booking_id, T.created, (sum(total) + 10) as total
from
  (select b.booking_id, p.created, (owr.own_res_total_in_site * own.own_commission_percent / 100) as total, own.own_mcp_code from booking b
    join ownershipreservation owr on owr.own_res_reservation_booking = b.booking_id
    join generalreservation gres on gres.gen_res_id = owr.own_res_gen_res_id
    join ownership own on own.own_id = gres.gen_res_own_id
    join payment p on p.booking_id = b.booking_id
  where p.created >= '2015-01-01' and p.created <= '2015-07-31'
   order by b.booking_id ASC) T
group by T.booking_id;
