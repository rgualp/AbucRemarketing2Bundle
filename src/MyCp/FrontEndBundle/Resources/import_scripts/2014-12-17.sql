﻿/*
Setting ranking with new formula for all accommodations in database
Author: Yanet Morales Ramirez
*/

update ownership o
set o.own_ranking = SQRT(
   (select sum(c.com_rate) + 1 from comment c where c.com_public = 1 and c.com_ownership = o.own_id) *
   ((select (count(gr.gen_res_id) + 1) * (count(gr.gen_res_id) + 1) from generalreservation gr where gr.gen_res_own_id = o.own_id AND gr.gen_res_status = 2))
   );

update ownership
   set own_sended_to_team = 1;