﻿/*
Re-Importing comments from old database to new database
Author: Yanet Morales Ramirez
*/

insert into mycasapa_prod.`user` (user_role,user_user_name,user_email, user_enabled, user_created_by_migration)
select DISTINCT "ROLE_CLIENT_TOURIST", c.com_name, c.com_email, 0, 1
from mycasapa_site.comentarios c
where not EXISTS (select * from mycasapa_prod.`user` where user_email = c.com_email);

insert into mycasapa_prod.`comment` (com_user, com_ownership, com_date, com_rate, com_public, com_comments)
select u.user_id,o.own_id,c.com_created,c.com_points, 1, c.com_comment
from mycasapa_site.comentarios c
join mycasapa_prod.`user` u on u.user_email = c.com_email
join mycasapa_site.propiedades p on p.pro_id = c.com_prop_id
join mycasapa_prod.ownership o on p.pro_code = o.own_mcp_code
where not EXISTS (select * from mycasapa_prod.`comment` com where com.com_user = u.user_id AND
com_ownership = o.own_id AND com_date = c.com_created AND com_rate = c.com_points AND com_comments = c.com_comment);

update mycasapa_prod.ownership o
set o.own_comments_total = (select count(*) from mycasapa_prod.`comment` c where c.com_ownership = o.own_id AND c.com_rate >= 3),
    o.own_rating = (select AVG(c.com_rate) from mycasapa_prod.`comment` c where o.own_id = c.com_ownership)