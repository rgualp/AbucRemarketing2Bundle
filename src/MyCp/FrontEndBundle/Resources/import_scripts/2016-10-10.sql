/**
 * Created by Avilio on 10/10/2016.
 */

SELECT DISTINCT o.`own_homeowner_1`, o.`own_email_1`
FROM ownership o
where o.own_status = 1 AND o.own_email_1 IS NOT NULL AND o.own_email_1 != ""
INTO OUTFILE 'Propietarios_email1.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n';

SELECT DISTINCT o.`own_homeowner_1`, o.`own_email_2`
FROM ownership o
where o.own_status = 1 AND o.own_email_2 IS NOT NULL AND o.own_email_2 != ""
INTO OUTFILE 'Propietarios_email12.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n';