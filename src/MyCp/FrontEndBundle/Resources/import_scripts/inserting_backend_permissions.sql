﻿/*
Inserting permissions for backend pages
Author: Yanet Morales Ramirez
*/

insert into permission (perm_description, perm_category, perm_route)
values ("Listar municipios", "Municipios", "mycp_list_municipality"),
("Agregar municipio", "Municipios", "mycp_new_municipality"),
("Editar municipio", "Municipios", "mycp_edit_municipality"),
("Eliminar municipio", "Municipios", "mycp_delete_municipality"),
("Listar alojamientos de un municipio", "Municipios", "mycp_list_ownerships_municipality"),
("Listar destinos de un municipio", "Municipios", "mycp_list_destinations_municipality");

insert rolepermission (rp_role, rp_permission)
values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_list_municipality')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_new_municipality')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_edit_municipality')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_delete_municipality')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_list_ownerships_municipality')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_list_destinations_municipality'));

insert into permission (perm_description, perm_category, perm_route)
values ("Listar temporadas", "Temporadas", "mycp_list_season"),
("Agregar temporada", "Temporadas", "mycp_new_season"),
("Editar temporada", "Temporadas", "mycp_edit_season"),
("Eliminar temporada", "Temporadas", "mycp_delete_season");

insert rolepermission (rp_role, rp_permission)
values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_list_season')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_new_season')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_edit_season')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_delete_season'));

