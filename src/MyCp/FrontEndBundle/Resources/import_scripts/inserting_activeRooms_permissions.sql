﻿/*
Inserting permissions for active-inactive rooms
Author: Yanet Morales Ramirez
*/

insert into permission (perm_description, perm_category, perm_route)
values ("Activar-Desactivar habitaciones", "Propiedades", "mycp_active_room");

insert rolepermission (rp_role, rp_permission)
values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_active_room')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_HOUSES_RESERVATIONS_COMMENTS'), (select max(perm_id) from permission where perm_route = 'mycp_active_room'));

