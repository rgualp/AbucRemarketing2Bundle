/*
Updating own_mobile_number field
Author: Yanet Morales Ramirez
*/

/*Trimming all 137 filas*/
update ownership set own_mobile_number = TRIM(own_mobile_number);

/*Preparing date. Replacing following characters: ., - and space*/
update ownership set own_mobile_number = REPLACE(own_mobile_number,'.',''); /*3 filas*/
update ownership set own_mobile_number = REPLACE(own_mobile_number,'-',''); /*240 filas*/
update ownership set own_mobile_number = REPLACE(own_mobile_number,' ',''); /*460 filas*/
update ownership set own_mobile_number = replace(own_mobile_number,'o',','); /*4 filas*/
update ownership set own_mobile_number = replace(own_mobile_number,'ó',','); /*5 filas*/

/*Case: (+53)5_______  14 filas*/
update ownership
set own_mobile_number = replace(own_mobile_number,'(+53)','')
where own_mobile_number != "" and own_mobile_number LIKE '(+53)5_______';

/*Case: (53)5_______ 8 filas*/
update ownership
set own_mobile_number = replace(own_mobile_number,'(53)','')
where own_mobile_number != "" and own_mobile_number LIKE '(53)5_______';

/*Case: (+535)_______ 61 filas*/
update ownership
set own_mobile_number = CONCAT("5", replace(own_mobile_number,'(+535)',''))
where own_mobile_number != "" and own_mobile_number LIKE '(+535)_______';

/*Case: (535)_______ 157 filas*/
update ownership
set own_mobile_number = CONCAT("5", replace(own_mobile_number,'(535)',''))
where own_mobile_number != "" and own_mobile_number LIKE '(535)_______';

/*Case: +(53)5_______ 55 filas*/
update ownership
set own_mobile_number = replace(own_mobile_number,'+(53)','')
where own_mobile_number != "" and own_mobile_number LIKE '+(53)5_______';

/*Case: +(535)_______ 9 filas*/
update ownership
set own_mobile_number = CONCAT("5", replace(own_mobile_number,'+(535)',''))
where own_mobile_number != "" and own_mobile_number LIKE '+(535)_______';

/*Case: +535_______ 74 filas*/
update ownership
set own_mobile_number = replace(own_mobile_number,'+53','')
where own_mobile_number != "" and own_mobile_number LIKE '+535_______';

/*Case: 535_______ 44 filas*/
update ownership
set own_mobile_number = SUBSTR(own_mobile_number,3,LENGTH(own_mobile_number))
where own_mobile_number != "" and own_mobile_number LIKE '535_______';

/*Case: (00535)_______ 1 fila*/
update ownership
set own_mobile_number = CONCAT("5", replace(own_mobile_number,'(00535)',''))
where own_mobile_number != "" and own_mobile_number LIKE '(00535)_______';

/*Case: (0053)5_______ 1 fila*/
update ownership
set own_mobile_number = CONCAT("5", replace(own_mobile_number,'(0053)',''))
where own_mobile_number != "" and own_mobile_number LIKE '(0053)5_______';

/*Case: 05_______ 3 filas*/
update ownership
set own_mobile_number = SUBSTR(own_mobile_number,2,LENGTH(own_mobile_number))
where own_mobile_number != "" and own_mobile_number LIKE '05_______';

/*Case: (05)_______ 1 fila*/
update ownership
set own_mobile_number = CONCAT("5", replace(own_mobile_number,'(05)',''))
where own_mobile_number != "" and own_mobile_number LIKE '(05)_______';