﻿/*
Consulta para volver a poner el nombre antiguo a las casas que salen con nombres duplicados
Author: Yanet Morales Ramirez
*/

drop table if exists names;
Create temporary table names (name VARCHAR(400));

INSERT INTO names (name)
(select distinct o.own_name as name from ownership o
where o.own_status = 1
group by o.own_name
having COUNT(o.own_id) > 1);

update ownership o1
set o1.own_name = o1.own_old_name
where o1.own_old_name is not null and
o1.own_status = 1 and
o1.own_name IN (select name from names)
;
drop table if exists names;

select o1.own_name, d.des_name, o1.own_old_name, s.`status_name`, o1.own_mcp_code from ownership o1
join destination d on d.des_id = o1.`own_destination`
join ownershipstatus s on s.`status_id` = o1.own_status
where o1.own_name IN
(select o.own_name as name from ownership o
where o.own_status = 1
group by o.own_name
having COUNT(o.own_id) > 1)
and o1.own_status = 1
order by o1.own_name;