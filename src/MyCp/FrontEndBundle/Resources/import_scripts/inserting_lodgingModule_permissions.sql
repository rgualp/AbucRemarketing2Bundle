/*
Permissions for Lodgin Module
Author: Yanet Morales Ramirez
*/

/*Deleting existing permissions*/
delete from rolepermission where rp_role = (select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA');

/*Adding new routes*/
insert into permission(perm_description, perm_category, perm_route)
values ('Comentarios', 'Módulo Casa', 'mycp_list_readonly_comments'),
('Editar Propiedad', 'Módulo Casa', 'mycp_short_edit_ownership'),
('Actualizar Propiedad', 'Módulo Casa', 'mycp_short_update_ownership'),
('Enviar correo propiedad', 'Módulo Casa', 'mycp_send_changes_mail_ownership'),
('Reservaciones', 'Módulo Casa', 'mycp_list_readonly_reservations'),
('Detalle de una reservación', 'Módulo Casa', 'mycp_details_readonly_reservation'),
('Insertar No Disponibilidad', 'Módulo Casa', 'mycp_lodging_new_unavailabilityDetails'),
('Editar No Disponibilidad', 'Módulo Casa', 'mycp_lodging_edit_unavailabilityDetails'),
('Eliminar No Disponibilidad', 'Módulo Casa', 'mycp_lodging_delete_unavailabilityDetails'),
('Calendario de No Disponibilidad', 'Módulo Casa', 'mycp_lodging_unavailabilityDetails_calendar'),
('Actualizar perfil de usuario', 'Módulo Casa', 'mycp_lodging_update_user'),
('Editar perfil de usuario', 'Módulo Casa', 'mycp_lodging_edit_user');

insert rolepermission (rp_role, rp_permission)
values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_list_readonly_comments')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_short_edit_ownership')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_short_update_ownership')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_send_changes_mail_ownership')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_list_readonly_reservations')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_details_readonly_reservation')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_lodging_new_unavailabilityDetails')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_lodging_edit_unavailabilityDetails')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_lodging_delete_unavailabilityDetails')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_lodging_unavailabilityDetails_calendar')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_lodging_update_user')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_CASA'), (select max(perm_id) from permission where perm_route = 'mycp_lodging_edit_user'));