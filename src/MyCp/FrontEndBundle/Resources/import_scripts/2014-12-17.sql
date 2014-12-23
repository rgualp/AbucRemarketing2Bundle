﻿/*
Setting ranking with new formula for all accommodations in database
Author: Yanet Morales Ramirez
*/

update ownership o
set o.own_ranking = SQRT(
   (select sum(c.com_rate) + 1 from comment c where c.com_public = 1 and c.com_ownership = o.own_id) *
   ((select (count(gr.gen_res_id) + 1) * (count(gr.gen_res_id) + 1) from generalreservation gr where gr.gen_res_own_id = o.own_id AND gr.gen_res_status = 2))
   );

update ownership
   set own_sended_to_team = 1;

/*Permissions for new routes in Lodging Module*/
insert into permission(perm_description, perm_category, perm_route)
values ('Reservaciones - Clientes', 'Módulo Casa', 'mycp_list_readonly_reservations_user'),
('Reservaciones - Detalle Clientes', 'Módulo Casa', 'mycp_details_readonly_client_reservation'),
('Clientes - Detalle de una reservación', 'Módulo Casa', 'mycp_details_readonly_reservation_partial');

insert rolepermission (rp_role, rp_permission)
values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_list_readonly_reservations_user')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_details_readonly_client_reservation')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_details_readonly_reservation_partial'));