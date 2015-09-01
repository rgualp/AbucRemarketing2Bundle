/*
List of accommodation's with emails
Author: Yanet Morales Ramirez
*/

select o.own_mcp_code as CODIGO, o.own_name as CASA, o.own_email_1 as CORREO1, o.own_email_2 as CORREO2,
       o.own_homeowner_1 as PROPIETARIO, o.own_homeowner_2 as PROPIETARIO2, p.prov_phone_code as CODIGO_TELEFONICO, o.own_phone_number as TELEFONO, o.own_mobile_number as MOVIL,
       o.own_address_street as CALLE, o.own_address_number as NUMERO, o.own_address_between_street_1 as ENTRE1, o.own_address_between_street_2 as ENTRE2,
       m.mun_name as MUNICIPIO, p.prov_name as PROVINCIA,
       o.own_saler as GESTOR
from ownership o
  join province p on o.own_address_province = p.prov_id
  join municipality m on m.mun_id = o.own_address_municipality
where o.own_status = 1 and (o.own_email_1 != "" or o.own_email_2 != "")
order by own_address_province, own_mcp_code
