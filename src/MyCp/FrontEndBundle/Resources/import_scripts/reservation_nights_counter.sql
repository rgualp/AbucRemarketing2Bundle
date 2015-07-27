﻿/*
Setting total nights in reservations
Author: Yanet Morales Ramirez
*/
update ownershipreservation ownRes
set ownRes.own_res_nights = DATEDIFF(ownRes.own_res_reservation_to_date,ownRes.own_res_reservation_from_date)
where ownRes.own_res_nights is null;

update generalreservation genRes
set genRes.gen_res_nights = (SELECT SUM(owRes.own_res_nights) FROM ownershipreservation owRes WHERE owRes.own_res_gen_res_id = genRes.gen_res_id)
WHERE genRes.gen_res_nights is null;