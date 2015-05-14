﻿/*
Accommodations without owner's photo
Author: Yanet Morales Ramirez
*/

select o.own_mcp_code as CODIGO, o.own_name as CASA, o.own_email_1 as CORREO1, o.own_email_2 as CORREO2, p.prov_phone_code as CODIGO_TELEFONICO, o.own_phone_number as TELEFONO, o.own_mobile_number as MOVIL
from ownership o
join province p on o.own_address_province = p.prov_id
where own_owner_photo is null
order by own_address_province, own_mcp_code
