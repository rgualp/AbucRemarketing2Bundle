﻿/*
Clients in Cuba in a selected date
Author: Yanet Morales Ramirez
*/

select u.user_id,CONCAT(u.user_user_name,' ', u.user_last_name) as Cliente, co.co_name as Pais, own.own_mcp_code as Alojamiento,
                 (select sum(owr1.own_res_nights) from ownershipreservation owr1 join generalreservation gres1 on owr1.own_res_gen_res_id = gres1.gen_res_id join booking b1 on b1.booking_id = owr1.own_res_reservation_booking
                   join payment pay1 on pay1.booking_id = b1.booking_id where owr1.own_res_status = 5 and u.user_id = gres1.gen_res_user_id and owr1.own_res_reservation_from_date >= '2014-10-01' and owr1.own_res_reservation_to_date <= '2014-12-01') as bookedNights,
                 (select count(distinct own2.own_address_province) from ownershipreservation owr2 join generalreservation gres2 on owr2.own_res_gen_res_id = gres2.gen_res_id join ownership own2 on own2.own_id = gres2.gen_res_own_id join booking b2 on b2.booking_id = owr2.own_res_reservation_booking
                   join payment pay2 on pay2.booking_id = b2.booking_id where owr2.own_res_status = 5 and u.user_id = gres2.gen_res_user_id and owr2.own_res_reservation_from_date >= '2014-10-01' and owr2.own_res_reservation_to_date <= '2014-12-01') as bookedDestinations
from ownershipreservation owr
  join generalreservation gres on owr.own_res_gen_res_id = gres.gen_res_id
  join user u on u.user_id = gres.gen_res_user_id
  join ownership own on own.own_id = gres.gen_res_own_id
  join country co on co.co_id = u.user_country
  join destination d on d.des_id = own.own_destination
  join booking b on b.booking_id = owr.own_res_reservation_booking
  join payment pay on pay.booking_id = b.booking_id
where owr.own_res_status = 5
      and own_res_reservation_from_date <= '2014-11-01' and own_res_reservation_to_date > '2014-11-01'
group by owr.own_res_gen_res_id;

/*
Itinerary of a client
*/
select distinct own1.* from ownershipreservation owr1
  join generalreservation gres1 on owr1.own_res_gen_res_id = gres1.gen_res_id
  join ownership own1 on own1.own_id = gres1.gen_res_own_id
  join booking b1 on b1.booking_id = owr1.own_res_reservation_booking
  join payment pay1 on pay1.booking_id = b1.booking_id
where owr1.own_res_status = 5
      and gres1.gen_res_user_id = 4666
      and owr1.own_res_reservation_from_date >= '2014-10-01'
      and owr1.own_res_reservation_to_date <= '2014-12-01'
order by owr1.own_res_reservation_from_date;

/*
This queries assumes a trip as all payed generalReservation in a range of 30 days before and 30 days after the selected date
*/