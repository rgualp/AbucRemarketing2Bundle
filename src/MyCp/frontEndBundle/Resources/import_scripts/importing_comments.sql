﻿/*
Importing comments from old database to new database
Author: Yanet Morales Ramirez
*/

use mypalada_mycp;

insert into mypalada_mycp.`user` (user_role,user_user_name,user_email, user_enabled, user_created_by_migration)
select DISTINCT "ROLE_CLIENT_TOURIST", c.com_name, c.com_email, 0, 1
from old_mypaladar_mycp.comentarios c
where not EXISTS (select * from mypalada_mycp.`user` where user_email = c.com_email);

insert into mypalada_mycp.`comment` (com_user, com_ownership, com_date, com_rate, com_public, com_comments)
select u.user_id,o.own_id,c.com_created,c.com_points, 1, c.com_comment
from old_mypaladar_mycp.comentarios c
join mypalada_mycp.`user` u on u.user_email = c.com_email
join old_mypaladar_mycp.propiedades p on p.pro_id = c.com_prop_id
join mypalada_mycp.ownership o on p.pro_code = o.own_mcp_code
where not EXISTS (select * from mypalada_mycp.`comment` com where com.com_user = u.user_id AND
com_ownership = o.own_id AND com_date = c.com_created AND com_rate = c.com_points AND com_comments = c.com_comment);

update mypalada_mycp.ownership o
set o.own_rating = (select AVG(c.com_rate) from mypalada_mycp.`comment` c where o.own_id = c.com_ownership)
where EXISTS (select * from mypalada_mycp.`comment` com where o.own_id = com.com_ownership);