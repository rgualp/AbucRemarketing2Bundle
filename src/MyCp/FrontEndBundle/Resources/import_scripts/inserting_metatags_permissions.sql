/*
Permissions for Metatags
Author: Yanet Morales Ramirez
*/

/*Adding new routes*/
insert into permission(perm_description, perm_category, perm_route)
values ('Listar metatags', 'Metatags', 'mycp_list_metatags'),
			 ('Editar metatag', 'Metatags', 'mycp_edit_metatags');

insert rolepermission (rp_role, rp_permission)
values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_list_metatags')),
((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_edit_metatags'));