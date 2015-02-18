﻿/*
SQL Script: Creating store procedure to update rooms numbers
Author: Yanet Morales Ramirez
*/
DELIMITER $$
DROP PROCEDURE IF EXISTS `setRoomNumbers` $$

CREATE DEFINER=`root`@`%` PROCEDURE `setRoomNumbers`()
begin

  DECLARE done INT DEFAULT FALSE;
  DECLARE cursor_own_id int;

  DECLARE own_cursor CURSOR FOR select own_id from ownership;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN own_cursor;

  read_loop: LOOP
    FETCH own_cursor INTO cursor_own_id;

    IF done THEN
      LEAVE read_loop;
    END IF;

   UPDATE room AS t,
       (SELECT @curRow :=@curRow+1 rownum, room_id, room_ownership
        FROM room, (select @curRow := 0) rn WHERE room_ownership = cursor_own_id) AS r
    SET t.room_num= r.rownum
    WHERE t.room_id = r.room_id;

  END LOOP;

  CLOSE own_cursor;

END $$

DELIMITER ;