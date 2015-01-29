﻿/*
SQL Script done since 2015-01-26
Author: Yanet Morales Ramirez
*/

/*Fixing dates in generalReservation*/
update generalreservation gr
set gr.gen_res_from_date = (SELECT MIN(ow.own_res_reservation_from_date) FROM ownershipreservation ow WHERE ow.own_res_gen_res_id =  gr.gen_res_id ),
		gr.gen_res_to_date = (SELECT MAX(ow1.own_res_reservation_to_date) FROM ownershipreservation ow1 WHERE ow1.own_res_gen_res_id =  gr.gen_res_id );

/*call setRoomNumbers;*/

/*Adding permissions for new backend routes*/
insert into permission(perm_description, perm_category, perm_route)
values ('Litas de correo', 'Listas de correo', 'mycp_list_mail_list'),
       ('Insertar lista de correo', 'Listas de correo', 'mycp_add_mail_list'),
       ('Editar lista de correo', 'Listas de correo', 'mycp_edit_mail_list'),
       ('Eliminar lista de correo', 'Listas de correo', 'mycp_delete_mail_list'),
       ('Listar usuarios', 'Usuarios de lista de correo', 'mycp_list_mail_list_user'),
       ('Insertar usuario', 'Usuarios de lista de correo', 'mycp_add_mail_list_user'),
       ('Eliminar usuario', 'Usuarios de lista de correo', 'mycp_delete_mail_list_user');

insert rolepermission (rp_role, rp_permission)
values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_list_mail_list')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_add_mail_list')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_edit_mail_list')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_delete_mail_list')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_list_mail_list_user')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_add_mail_list_user')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_delete_mail_list_user'));