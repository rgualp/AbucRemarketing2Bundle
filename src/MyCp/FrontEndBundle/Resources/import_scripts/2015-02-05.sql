﻿/*
SQL Script done since 2015-02-05
Author: Yanet Morales Ramirez
*/

/*Adding permissions for backend export routes*/
insert into permission(perm_description, perm_category, perm_route)
values ('Exportar a Excel', 'Exportar a Excel', 'mycp_accommodations_export_excel');

insert rolepermission (rp_role, rp_permission)
values ((select max(role_id) from role where role_name = 'ROLE_CLIENT_STAFF'), (select max(perm_id) from permission where perm_route = 'mycp_accommodations_export_excel'));

update ownership
   set own_publish_date = own_creation_date
where own_creation_date is not null and own_status = 1;

update ownership
set own_inmediate_booking = false;

