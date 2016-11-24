﻿/*
Author: Yanet Morales Ramirez
*/

/*5 casas menos reservadas por destinos*/
set @num := 0, @type := '';

select des_name, own_mcp_code, reservedRooms
from (
   select T.*,
      @num := if(@type = T.des_name, @num + 1, 1) as row_number,
      @type := T.des_name as destination
  from
  (select d.des_name, o.own_mcp_code, od.reservedRooms
  from ownership o
  join ownershipdata od on o.own_id = od.accommodation
  join destination d on d.des_id = o.own_destination
  where od.reservedRooms is not null AND od.reservedRooms > 0 AND od.reservedRooms < 50
  order by d.des_name, od.reservedRooms ASC
  ) T
  order by row_number ASC
) as x
where x.row_number <= 5
order by des_name, reservedRooms ASC
;

/*5 casas mas resevadas por destinos*/
set @num := 0, @type := '';

select des_name, own_mcp_code, reservedRooms
from (
   select T.*,
      @num := if(@type = T.des_name, @num + 1, 1) as row_number,
      @type := T.des_name as destination
  from
  (select d.des_name, o.own_mcp_code, od.reservedRooms
  from ownership o
  join ownershipdata od on o.own_id = od.accommodation
  join destination d on d.des_id = o.own_destination
  where od.reservedRooms is not null AND od.reservedRooms >= 50
  order by d.des_name, od.reservedRooms DESC
  ) T
  order by row_number ASC
) as x
where x.row_number <= 5
order by des_name, reservedRooms DESC
;