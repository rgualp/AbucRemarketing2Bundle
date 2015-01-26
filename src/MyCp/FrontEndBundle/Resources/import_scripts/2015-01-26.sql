﻿/*
SQL Script done since 2015-01-26
Author: Yanet Morales Ramirez
*/

/*Fixing dates in generalReservation*/
update generalreservation gr
set gr.gen_res_from_date = (SELECT MIN(ow.own_res_reservation_from_date) FROM ownershipreservation ow WHERE ow.own_res_gen_res_id =  gr.gen_res_id ),
		gr.gen_res_to_date = (SELECT MAX(ow1.own_res_reservation_to_date) FROM ownershipreservation ow1 WHERE ow1.own_res_gen_res_id =  gr.gen_res_id );

