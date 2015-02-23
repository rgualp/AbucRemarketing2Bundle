﻿/*
SQL Script done since 2015-02-23
Author: Yanet Morales Ramirez
*/

/*If all ownershipReservations associated to a generalREservation are cancelled, then the status of the generalReservation must be set to cancelled*/
update generalreservation gr
set gr.gen_res_status =  6
where (select count(*) from ownershipreservation ow where ow.own_res_status = 4 and ow.own_res_gen_res_id = gr.gen_res_id) = (select count(*) from ownershipreservation ow1 where ow1.own_res_gen_res_id = gr.gen_res_id)


