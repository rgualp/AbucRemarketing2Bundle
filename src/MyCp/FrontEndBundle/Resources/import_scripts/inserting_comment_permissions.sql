/*
Permissions for new routes in comments. Giving permission to comment pages for roles: ROLE_CLIENT_STAFF, ROLE_CLIENT_STAFF_RESERVATION_TEAM and ROLE_CLIENT_STAFF_HOUSES_RESERVATIONS
Author: Yanet Morales Ramirez
*/

/*Adding new routes*/
insert into permission(perm_description, perm_category, perm_route)
values ('Publicar comentario', 'Comentarios', 'mycp_public_comment'),
			 ('Lista últimos comentarios', 'Comentarios', 'mycp_last_comment');

insert rolepermission (rp_role, rp_permission)
values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_public_comment')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_last_comment')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM'), (select max(perm_id) from permission where perm_route = 'mycp_public_comment')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM'), (select max(perm_id) from permission where perm_route = 'mycp_last_comment')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_HOUSES_RESERVATIONS'), (select max(perm_id) from permission where perm_route = 'mycp_public_comment')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_HOUSES_RESERVATIONS'), (select max(perm_id) from permission where perm_route = 'mycp_last_comment'));

insert rolepermission (rp_role, rp_permission)
values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM'), (select max(perm_id) from permission where perm_route = 'mycp_list_comments')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM'), (select max(perm_id) from permission where perm_route = 'mycp_edit_comment')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_RESERVATION_TEAM'), (select max(perm_id) from permission where perm_route = 'mycp_delete_comment'));

insert rolepermission (rp_role, rp_permission)
values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_HOUSES_RESERVATIONS'), (select max(perm_id) from permission where perm_route = 'mycp_list_comments')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_HOUSES_RESERVATIONS'), (select max(perm_id) from permission where perm_route = 'mycp_edit_comment')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF_HOUSES_RESERVATIONS'), (select max(perm_id) from permission where perm_route = 'mycp_delete_comment'));