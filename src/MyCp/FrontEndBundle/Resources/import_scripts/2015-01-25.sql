﻿/*
SQL Script: Creating store procedure to update rooms numbers
Author: Yanet Morales Ramirez
*/

/*TODO: Buscar una solucion q me permita actualizar el elemento en curso en el cursor*/

CREATE PROCEDURE `setRoomNumbers`()
begin

  DECLARE done INT DEFAULT FALSE;
  DECLARE cursor_room_id, cursor_ownership_id, cursor_room_number, ownership_id, room_number int;	
	
  DECLARE rooms_cursor CURSOR FOR select room_id, room_ownership, room_num
  from room order by room_ownership, room_num ASC;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN rooms_cursor;

	set ownership_id = 0;
	set room_number = 0;

  read_loop: LOOP
    FETCH rooms_cursor INTO cursor_room_id, cursor_ownership_id, cursor_room_number;

    IF done THEN
      LEAVE read_loop;
    END IF;

	if ownership_id = 0 or cursor_ownership_id <> ownership_id THEN
		set ownership_id = cursor_ownership_id;
	  set room_number = 1;
	end if;

	update room r
		set r.room_num = room_number
  where r.room_id = cursor_room_id;

	set room_number = room_number + 1;
    
  END LOOP;

  CLOSE rooms_cursor;

end