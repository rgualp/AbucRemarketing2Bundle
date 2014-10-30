﻿/*
Re-Calculating Properties' Capacities
Author: Yanet Morales Ramirez
*/

update ownership o
   set o.own_maximun_number_guests = ((select count(*) from room r where r.room_ownership = o.own_id and r.room_type LIKE 'Habitación individual') +
(select 2 * count(*) from room r where r.room_ownership = o.own_id and r.room_type LIKE '%Habitación doble%') +
(select 3 * count(*) from room r where r.room_ownership = o.own_id and r.room_type LIKE 'Habitación Triple'));