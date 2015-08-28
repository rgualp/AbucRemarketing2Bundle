/*
Queries for Excel File
Author: Yanet Morales Ramirez
*/

/*List of translated accommodations to all languages*/
select o.own_mcp_code, o.own_name from ownership o
where o.own_status = 1
and (select count(*) from ownershipdescriptionlang odl where odl.odl_id_ownership = o.own_id and odl.odl_description <> "") >= 3;

/*List of non translated accommodations*/
select o.own_mcp_code, o.own_name from ownership o
where o.own_status = 1
      and (select count(*) from ownershipdescriptionlang odl where odl.odl_id_ownership = o.own_id and odl.odl_description <> "") < 3;

/*List of accommodations translated to english and then to german automatically*/
select o.own_mcp_code, o.own_name from ownership o
where o.own_status = 1
      and (select count(*) from ownershipdescriptionlang odl where odl.odl_id_ownership = o.own_id and odl.odl_description <> "" and odl.odl_id_lang = 20) > 0
      and (select count(*) from ownershipdescriptionlang odl where odl.odl_id_ownership = o.own_id and odl.odl_description <> "" and odl.odl_id_lang = 21 and odl.odl_automatic_translation = 1) > 0;
