insert into mycasapa_prod.ownership
(own_name,
   own_address_province,
   own_address_municipality,
   own_mcp_code,
   own_address_street,
   own_mobile_number,
   own_homeowner_1,
   own_phone_number,
   own_email_1,
   own_email_2,
   own_type,
   own_top_20,
   own_phone_code,
   own_status,
   own_commission_percent,
   own_creation_date)
  select
  prop.pro_name,
  (select min(m.mun_prov_id) from mycasapa_prod.municipality m where m.mun_name = mun.mun_name) as prov_id,
  (select min(m.mun_id) from mycasapa_prod.municipality m where m.mun_name = mun.mun_name) as mun_id,
  prop.pro_code,
  prop.pro_address,
  prop.pro_cell,
  prop.pro_host,
  prop.pro_phone,
  prop.pro_email,
  prop.pro_email1,
  "Casa particular",
  prop.pro_top20,
  (select min(prov.prov_phone_code) from mycasapa_prod.municipality m join mycasapa_prod.province prov on m.mun_prov_id = prov.prov_id where m.mun_name = mun.mun_name),
  (select min(os.status_id) from mycasapa_prod.ownershipstatus os where os.status_name = 'En proceso (migración)'),
  prop.pro_offer_type,
  prop.pro_fecha_creacion
  from mycasapa_site.propiedades prop
    join mycasapa_site.municipios mun on prop.pro_mun_id = mun.mun_id
  where prop.pro_code not in (select ownership.own_mcp_code from mycasapa_prod.ownership);