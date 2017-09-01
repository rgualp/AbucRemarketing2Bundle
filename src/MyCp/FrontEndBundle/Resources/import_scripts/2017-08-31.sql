﻿/*
Consultas para ver casas a las que les falta la traduccion
Author: Yanet Morales Ramirez
*/

select * from
(select o.own_name, o.own_mcp_code, os.status_name,
(select COUNT(*) from ownershipdescriptionlang odl where odl.odl_id_ownership = o.own_id) as trans
from ownership o
join ownershipstatus os on os.status_id = o.own_status
) T
where T.trans < 5
order by T.status_name ASC;
