/*
Updating own_mobile_number field
Author: Yanet Morales Ramirez
*/

/*Trimming all*/
update ownership set own_mobile_number = TRIM(own_mobile_number);

/*Preparing date. Replacing following characters: ., - and space*/
update ownership set own_mobile_number = REPLACE(own_mobile_number,'.','');
update ownership set own_mobile_number = REPLACE(own_mobile_number,'-','');
update ownership set own_mobile_number = REPLACE(own_mobile_number,' ','');

/*Case: (+53)5_______  */
update ownership
set own_mobile_number = replace(own_mobile_number,'(+53)','')
where own_mobile_number != "" and own_mobile_number LIKE '(+53)5_______';

/*Case: (53)5_______*/
update ownership
set own_mobile_number = replace(own_mobile_number,'(53)','')
where own_mobile_number != "" and own_mobile_number LIKE '(53)5_______';

/*Case: (+535)_______*/
update ownership
set own_mobile_number = CONCAT("5", replace(own_mobile_number,'(+535)',''))
where own_mobile_number != "" and own_mobile_number LIKE '(+535)_______';

/*Case: (535)_______*/
update ownership
set own_mobile_number = CONCAT("5", replace(own_mobile_number,'(535)',''))
where own_mobile_number != "" and own_mobile_number LIKE '(535)_______';

/*Case: +(53)5_______*/
update ownership
set own_mobile_number = replace(own_mobile_number,'+(53)','')
where own_mobile_number != "" and own_mobile_number LIKE '+(53)5_______';

/*Case: +(535)_______*/
update ownership
set own_mobile_number = CONCAT("5", replace(own_mobile_number,'+(535)',''))
where own_mobile_number != "" and own_mobile_number LIKE '+(535)_______';

/*Case: +535_______*/
update ownership
set own_mobile_number = CONCAT("5", replace(own_mobile_number,'+53',''))
where own_mobile_number != "" and own_mobile_number LIKE '+535_______';

/*Case: 535_______*/
update ownership
set own_mobile_number = SUBSTR(own_mobile_number,3,LENGTH(own_mobile_number))
where own_mobile_number != "" and own_mobile_number LIKE '535_______';

/*Case: (00535)_______*/
update ownership
set own_mobile_number = CONCAT("5", replace(own_mobile_number,'(00535)',''))
where own_mobile_number != "" and own_mobile_number LIKE '(00535)_______';

/*Case: (0053)5_______*/
update ownership
set own_mobile_number = CONCAT("5", replace(own_mobile_number,'(0053)',''))
where own_mobile_number != "" and own_mobile_number LIKE '(0053)5_______';