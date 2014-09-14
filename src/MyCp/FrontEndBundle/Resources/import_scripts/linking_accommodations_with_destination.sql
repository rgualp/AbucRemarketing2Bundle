﻿/*
Linking every accommodation in database with a destination located in the accommodation's municipality
Author: Yanet Morales Ramirez
*/

update ownership o
   set o.own_destination = (select min(d.des_id) from destinationlocation dl
			    join destination d on dl.des_loc_des_id = d.des_id
			    where dl.des_loc_mun_id = o.own_address_municipality);