﻿/*
Importing data from old database to new database
Author: Yanet Morales Ramirez
*/

/*
Drop data from new database
*/
use mypalada_mycp;
delete from favorite;
delete from userhistory;
delete from ownershipreservation;
delete from generalreservation;
delete from ownershipdescriptionlang;
delete from room;
delete from comment;
delete from ownershipkeywordlang;
delete from ownershipphoto;
delete from usercasa;
delete from ownership;
delete from destinationlocation;
delete from destinationlang;
delete from destinationlocation;
delete from destinationphoto;
delete from destination;
delete from municipality;
delete from photolang;
delete from albumcategorylang;
delete from albumlang;
delete from albumphoto;
delete from album;
delete from albumcategory;
delete from photo where pho_name NOT LIKE '%flag-%' AND pho_name NOT LIKE '%user-%';
delete from faqlang;
delete from faq;
delete from faqcategorylang;
delete from faqcategory;

/*
Auxiliar columns
*/
SELECT count(*) into @exist FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='mypalada_mycp' AND TABLE_NAME = 'photo' AND COLUMN_NAME = 'import_id';
set @query = IF(@exist <= 0, 'alter table mypalada_mycp.photo ADD COLUMN import_id int', 
'select \'Column Exists\' status');
prepare stmt from @query;
EXECUTE stmt;

SELECT count(*) into @exist FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='mypalada_mycp' AND TABLE_NAME = 'photo' AND COLUMN_NAME = 'element_id';
set @query = IF(@exist <= 0, 'alter table mypalada_mycp.photo ADD COLUMN element_id int', 
'select \'Column Exists\' status');
prepare stmt from @query;
EXECUTE stmt;

SELECT count(*) into @exist FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='mypalada_mycp' AND TABLE_NAME = 'photo' AND COLUMN_NAME = 'element_type';
set @query = IF(@exist <= 0, 'alter table mypalada_mycp.photo ADD COLUMN element_type varchar(100)', 
'select \'Column Exists\' status');
prepare stmt from @query;
EXECUTE stmt;

SELECT count(*) into @exist FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='mypalada_mycp' AND TABLE_NAME = 'province' AND COLUMN_NAME = 'import_id';
set @query = IF(@exist <= 0, 'alter table mypalada_mycp.province ADD COLUMN import_id int', 
'select \'Column Exists\' status');
prepare stmt from @query;
EXECUTE stmt;

SELECT count(*) into @exist FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='mypalada_mycp' AND TABLE_NAME = 'lang' AND COLUMN_NAME = 'import_id';
set @query = IF(@exist <= 0, 'alter table mypalada_mycp.lang ADD COLUMN import_id int', 
'select \'Column Exists\' status');
prepare stmt from @query;
EXECUTE stmt;

SELECT count(*) into @exist FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='mypalada_mycp' AND TABLE_NAME = 'faq' AND COLUMN_NAME = 'import_id';
set @query = IF(@exist <= 0, 'alter table mypalada_mycp.faq ADD COLUMN import_id int', 
'select \'Column Exists\' status');
prepare stmt from @query;
EXECUTE stmt;
/*
Import provinces
*/
update mypalada_mycp.province p
set import_id = (select r.reg_id 
from old_mypaladar_mycp.regiones r
where p.prov_name = replace(r.reg_name,'.','')
limit 0,1 
);

/*
Import municipalities
*/
insert into mypalada_mycp.municipality (mun_id,mun_prov_id,mun_name)
select o.mun_id, n.prov_id, o.mun_name
from old_mypaladar_mycp.municipios o
join mypalada_mycp.province n on o.mun_reg_id = n.import_id;

/*
Import languages - process is manual because i didn't find a match point between both tables
*/
update mypalada_mycp.lang set import_id = (select lang_id from old_mypaladar_mycp.idiomas where lang_abr = "eng") where lang_code = "EN";
update mypalada_mycp.lang set import_id = (select lang_id from old_mypaladar_mycp.idiomas where lang_abr = "esp") where lang_code = "ES";
update mypalada_mycp.lang set import_id = (select lang_id from old_mypaladar_mycp.idiomas where lang_abr = "ger") where lang_code = "DE";

/*
Importing albums category
*/
insert into mypalada_mycp.albumcategory (alb_cat_id)
select ca_id
from old_mypaladar_mycp.categorias_albumes;

/*
Importing albums category lang
*/
insert into mypalada_mycp.albumcategorylang (album_cat_id, album_cat_id_lang, album_cat_id_cat, album_cat_description)
select o.ca_lang_id, n.lang_id, o.ca_lang_ca_id, o.ca_lang_name 
from old_mypaladar_mycp.categorias_albumes_lang o
join mypalada_mycp.lang n on n.import_id = o.ca_lang_lang_id;

/*
Importing albums
*/
insert into mypalada_mycp.album (album_id, album_alb_cat_id, album_name, album_order, album_active)
select a.alb_id, a.alb_cat_id, al.alb_lang_name, a.alb_order, a.alb_active
from old_mypaladar_mycp.albumes a
join old_mypaladar_mycp.albumes_lang al on a.alb_id = al.alb_lang_alb_id
join mypalada_mycp.lang l on l.import_id = al.alb_lang_lang_id
where l.lang_code = "ES";

/*
Importing albums lang
*/
insert into mypalada_mycp.albumlang (album_lang_id, album_lang_album_id, album_lang_lang_id, album_lang_name, album_lang_brief_description)
select o.alb_lang_id, o.alb_lang_alb_id, n.lang_id, o.alb_lang_name, o.alb_lang_brief
from old_mypaladar_mycp.albumes_lang o
join mypalada_mycp.lang n on n.import_id = o.alb_lang_lang_id;

/*
Importing album photos
*/
insert into mypalada_mycp.photo (pho_name, pho_order, import_id, element_id, element_type)
select f.fa_img, f.fa_order, f.fa_id, f.fa_alb_id, 'album'
from old_mypaladar_mycp.fotografias_albumes f;

insert into mypalada_mycp.photolang(pho_lang_id_lang, pho_lang_id_photo, pho_lang_description)
select l.lang_id, p.pho_id, al.fa_lang_title
from old_mypaladar_mycp.fotografias_albumes_lang al 
join mypalada_mycp.photo p on al.fa_lang_fa_id = p.import_id
join mypalada_mycp.lang l on l.import_id = al.fa_lang_lang_id
where p.element_type = 'album' and al.fa_lang_title <> "";

insert into mypalada_mycp.albumphoto (alb_pho_pho_id, alb_pho_alb_id)
select p.pho_id, f.fa_alb_id
from old_mypaladar_mycp.fotografias_albumes f
join mypalada_mycp.photo p on f.fa_id = p.import_id
where p.element_type = 'album';

/*
Importing destination
*/
insert into mypalada_mycp.destination (des_id, des_name, des_order, des_active)
select d.des_id, d.des_name, d.des_order, d.des_active
from old_mypaladar_mycp.destinos d;

insert into mypalada_mycp.destinationlang (des_lang_id, des_lang_des_id, des_lang_lang_id, des_lang_brief, des_lang_desc)
select o.des_lang_id, o.des_lang_des_id, n.lang_id, o.des_lang_brief, o.des_lang_desc
from old_mypaladar_mycp.destinos_lang o
join mypalada_mycp.lang n on n.import_id = o.des_lang_lang_id
join mypalada_mycp.destination d on d.des_id = o.des_lang_des_id;

insert into mypalada_mycp.destinationlocation (des_loc_des_id, des_loc_mun_id, des_loc_prov_id)
select dm.rdm_des_id, dm.rdm_mun_id, m.mun_prov_id
from old_mypaladar_mycp.rel_destinos_municipios dm
join mypalada_mycp.municipality m on dm.rdm_mun_id = m.mun_id
join mypalada_mycp.destination d on d.des_id = dm.rdm_des_id;

insert into mypalada_mycp.photo (pho_name, pho_order, import_id, element_id, element_type)
select f.fd_img, f.fd_order, f.fd_id, f.fd_des_id, 'destination'
from old_mypaladar_mycp.fotografias_destinos f;

insert into mypalada_mycp.photolang(pho_lang_id_lang, pho_lang_id_photo, pho_lang_description)
select l.lang_id, p.pho_id, dl.fd_lang_title
from old_mypaladar_mycp.fotografias_destinos_lang dl 
join mypalada_mycp.photo p on dl.fd_lang_fd_id = p.import_id
join mypalada_mycp.lang l on l.import_id =dl.fd_lang_lang_id
where p.element_type = 'destination' and dl.fd_lang_title <> "";

insert into mypalada_mycp.destinationphoto (des_pho_pho_id, des_pho_des_id)
select p.pho_id, f.fd_des_id
from old_mypaladar_mycp.fotografias_destinos f
join mypalada_mycp.photo p on f.fd_id = p.import_id
where p.element_type = 'destination';

/*
Importing ownerships
*/
insert into mypalada_mycp.ownership (
own_id,
own_name,
own_address_province,
own_address_municipality,
own_mcp_code,
own_address_street,
own_address_number,
own_address_between_street_1,
own_address_between_street_2,
own_licence_number,
own_mobile_number,
own_homeowner_1,
own_homeowner_2,
own_phone_number,
own_email_1,
own_email_2,
own_category,
own_type,
own_top_20,
own_phone_code,
own_status,
own_water_jacuzee,
own_water_sauna,
own_water_piscina,
own_description_internet,
own_facilities_notes,
own_facilities_breakfast,
own_facilities_breakfast_price,
own_facilities_dinner,
own_facilities_dinner_price_from,
own_facilities_dinner_price_to,
own_facilities_parking,
own_facilities_parking_price,
own_description_bicycle_parking,
own_description_pets,
own_description_laundry,
own_commission_percent)
select p.pro_id,
p.pro_name,
m.mun_prov_id,
p.pro_mun_id,
p.pro_code,
otros.calle,
otros.numero,
otros.entrecalles,
otros.entrecalles2,
otros.nolicencia,
p.pro_cell,
p.pro_host,
otros.propietario2,
p.pro_phone,
p.pro_email,
p.pro_email1,
IF(pr.pre_label = "Económicas", "Económica", pr.pre_label),
"Casa particular",
p.pro_top20,
prov.prov_phone_code,
IF(p.pro_active, 1, 2),
otros.jacuzee,
otros.sauna,
otros.piscina,
otros.internet,
otros.notas,
otros.desayunoinc,
otros.desayunoprecio,
otros.cenainc,
otros.cenadesde,
otros.cenahasta,
otros.parqueoinc,
otros.parqueoprec,
otros.parqueociclos,
otros.mascotas,
otros.lavanderia,
p.pro_offer_type
from old_mypaladar_mycp.propiedades p
join mypalada_mycp.municipality m on p.pro_mun_id = m.mun_id
join mypalada_mycp.province prov on m.mun_prov_id = prov.prov_id
join old_mypaladar_mycp.precios pr on p.pro_range_id = pr.pre_range_id
join mypalada_mycp.lang l on l.import_id = pr.pre_lang_id
join old_mypaladar_mycp.pro_otros otros on otros.id_propiedad = p.pro_code
where l.lang_code = "ES";

update mypalada_mycp.ownership own
set own.own_geolocate_y = (select g.latitud from old_mypaladar_mycp.pro_geotag g where own.own_id = g.idCasa limit 0,1),
    own.own_geolocate_x = (select g.longitud from old_mypaladar_mycp.pro_geotag g where own.own_id = g.idCasa limit 0,1);

update mypalada_mycp.ownership own
set own.own_langs = concat(if((select count(*) from old_mypaladar_mycp.prop_lang pl join mypalada_mycp.lang l on pl.idLang = l.import_id where own.own_id = pl.idProp and upper(l.lang_code) = "EN") > 0, "1", "0")
									, if((select count(*) from old_mypaladar_mycp.prop_lang pl join mypalada_mycp.lang l on pl.idLang = l.import_id where own.own_id = pl.idProp and  upper(l.lang_code) = "FR") > 0, "1", "0")
									, if((select count(*) from old_mypaladar_mycp.prop_lang pl join mypalada_mycp.lang l on pl.idLang = l.import_id where own.own_id = pl.idProp and  upper(l.lang_code) = "DE") > 0, "1", "0")
									, if((select count(*) from old_mypaladar_mycp.prop_lang pl join mypalada_mycp.lang l on pl.idLang = l.import_id where own.own_id = pl.idProp and  upper(l.lang_code) = "IT") > 0, "1", "0"));

insert into mypalada_mycp.ownershipkeywordlang (okl_id_lang, okl_id_ownership, okl_keywords)
select l.lang_id, own.own_id, plang.pro_lang_keywords
from old_mypaladar_mycp.propiedades_lang plang 
join mypalada_mycp.lang l on plang.pro_lang_lang_id = l.import_id
join mypalada_mycp.ownership own on own.own_id = plang.pro_lang_pro_id;

insert into mypalada_mycp.ownershipdescriptionlang (odl_id_lang, odl_id_ownership, odl_description, odl_brief_description)
select l.lang_id, plang.pro_lang_pro_id, plang.pro_lang_desc, plang.pro_lang_brief
from old_mypaladar_mycp.propiedades_lang plang 
join mypalada_mycp.lang l on plang.pro_lang_lang_id = l.import_id
join mypalada_mycp.ownership own on own.own_id = plang.pro_lang_pro_id;

insert into mypalada_mycp.room (
room_id,
room_ownership,
room_type,
room_beds,
room_price_up_from,
room_price_up_to,
room_price_down_from,
room_price_down_to,
room_climate,
room_audiovisual,
room_smoker,
room_safe,
room_baby,
room_bathroom,
room_stereo,
room_windows,
room_balcony,
room_terrace,
room_yard
)
select id_hab,
own.own_id,
tipo_hab,
cant_camas,
alta_desde,
alta_hasta,
baja_desde,
baja_hasta,
climatizacion,
audiovis,
fumador,
cajafuerte,
facilidadesbb,
tipo_banno,
estereo,
ventana,
balcon,
terraza,
patio
from old_mypaladar_mycp.pro_habitaciones h
join mypalada_mycp.ownership own on h.pro_code = own.own_mcp_code;

update mypalada_mycp.ownership own
set own.own_maximun_number_guests = (select sum(room_beds) from mypalada_mycp.room r where r.room_ownership = own.own_id);

update mypalada_mycp.ownership own
set own.own_rooms_total = (select count(*) from mypalada_mycp.room r where r.room_ownership = own.own_id);

update mypalada_mycp.ownership own
set own.own_maximum_price = (select max(r.room_price_up_to) from mypalada_mycp.room r where r.room_ownership = own.own_id);

update mypalada_mycp.ownership own
set own.own_minimum_price = (select min(r.room_price_down_from) from mypalada_mycp.room r where r.room_ownership = own.own_id);

update mypalada_mycp.ownership own
set own.own_minimum_price = (select p.pro_prec_temp_baja from old_mypaladar_mycp.propiedades p where p.pro_id = own.own_id limit 0,1)
where own.own_minimum_price = 0;

update mypalada_mycp.ownership own
set own.own_maximum_price = (select p.pro_prec_temp_alta from old_mypaladar_mycp.propiedades p where p.pro_id = own.own_id limit 0,1)
where own.own_maximum_price = 0;

insert into mypalada_mycp.photo (pho_name, pho_order, import_id, element_id, element_type)
select f.fot_img, f.fot_order, f.fot_id, f.fot_pro_id, 'propiedad'
from old_mypaladar_mycp.fotografias f;

insert into mypalada_mycp.photolang(pho_lang_id_lang, pho_lang_id_photo, pho_lang_description)
select l.lang_id, p.pho_id, dl.fot_lang_title
from old_mypaladar_mycp.fotografias_lang dl 
join mypalada_mycp.photo p on dl.fot_lang_fot_id = p.import_id
join mypalada_mycp.lang l on l.import_id =dl.fot_lang_lang_id
where p.element_type = 'propiedad' and dl.fot_lang_title <> "";

insert into mypalada_mycp.ownershipphoto (own_pho_pho_id, own_pho_own_id)
select p.pho_id, p.element_id
from old_mypaladar_mycp.fotografias f
join mypalada_mycp.photo p on f.fot_id = p.import_id
join mypalada_mycp.ownership own on own.own_id = p.element_id
where p.element_type = 'propiedad';

/*
Importing FAQ
*/
insert into mypalada_mycp.faqcategory (faq_cat_id)
select preg.pre_id from old_mypaladar_mycp.preguntas preg;

insert into mypalada_mycp.faqcategorylang (faq_cat_id_cat,faq_cat_description,faq_cat_id_lang)
select pl.pre_lang_pre_id, pl.pre_lang_question, l.lang_id 
from old_mypaladar_mycp.preguntas_lang pl
join mypalada_mycp.faqcategory fc on fc.faq_cat_id = pl.pre_lang_pre_id
join mypalada_mycp.lang l on pl.pre_lang_lang_id = l.import_id;

insert into mypalada_mycp.faq (faq_order, faq_active, faq_faq_cat_id, import_id)
select p.pre_order, p.pre_active, p.pre_id, p.pre_id
from old_mypaladar_mycp.preguntas p;

insert into mypalada_mycp.faqlang (faq_lang_faq_id, faq_lang_lang_id, faq_lang_question, faq_lang_answer)
select f.faq_id,l.lang_id,plang.pre_lang_question, plang.pre_lang_answer
from old_mypaladar_mycp.preguntas_lang plang
join mypalada_mycp.lang l on l.import_id = plang.pre_lang_lang_id
join mypalada_mycp.faq f on f.import_id = plang.pre_lang_pre_id;

/*
Deleting auxiliar columnsp
*/

ALTER TABLE mypalada_mycp.photo DROP COLUMN import_id;
ALTER TABLE mypalada_mycp.photo DROP COLUMN element_id;
ALTER TABLE mypalada_mycp.photo DROP COLUMN element_type;
ALTER TABLE mypalada_mycp.province DROP COLUMN import_id;
ALTER TABLE mypalada_mycp.lang DROP COLUMN import_id;
ALTER TABLE mypalada_mycp.faq DROP COLUMN import_id;