/*
Reportes - Reservas
Author: Yanet Morales Ramirez
*/
/*General turistas por años*/
select Anno, Adults + Kids as Tourists from
  (select YEAR(genRes.gen_res_date) as Anno, sum(owres.`own_res_count_adults`) as Adults,
          SUM(owres.`own_res_count_childrens`) as Kids
   from ownershipreservation owres
     join generalreservation genRes on genRes.gen_res_id = owres.own_res_gen_res_id
   where owres.own_res_status = 5
   GROUP BY Year(genRes.gen_res_date)
   ORDER BY Year(genRes.gen_res_date)) T;

/*General número de noches reservadas por habitaciones por años*/
select YEAR(genRes.gen_res_date) as Anno, sum(DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date) - 1) as Noches
from ownershipreservation owres
  join generalreservation genRes on genRes.gen_res_id = owres.own_res_gen_res_id
where owres.own_res_status = 5
GROUP BY Year(genRes.gen_res_date)
ORDER BY Year(genRes.gen_res_date);

/*General facturacion por años*/
select YEAR(genRes.gen_res_date) as Anno, sum(owres.`own_res_total_in_site`* (1 - o.`own_commission_percent`/100)) as Facturacion
from ownershipreservation owres
  join generalreservation genRes on genRes.gen_res_id = owres.own_res_gen_res_id
  join ownership o on o.own_id = genRes.`gen_res_own_id`
where owres.own_res_status = 5 and owres.`own_res_reservation_booking` is not null
GROUP BY Year(genRes.gen_res_date)
ORDER BY Year(genRes.gen_res_date);

/*General total de casas por años*/
select YEAR(o.`own_creation_date`) as Anno, count(*)
from ownership o
GROUP BY Year(o.`own_creation_date`)
ORDER BY Year(o.`own_creation_date`);

/*General total de reservaciones por años (bookings)*/
select YEAR(genRes.gen_res_date) as Anno, count(distinct owres.`own_res_reservation_booking`) as Facturacion
from ownershipreservation owres
  join generalreservation genRes on genRes.gen_res_id = owres.own_res_gen_res_id
  join ownership o on o.own_id = genRes.`gen_res_own_id`
where owres.own_res_status = 5 and owres.`own_res_reservation_booking` is not null
GROUP BY Year(genRes.gen_res_date)
ORDER BY Year(genRes.gen_res_date);


/*General total de solicitudes por casas por años*/
select YEAR(genRes.gen_res_date) as Anno, count(*) as Solicitudes
from generalreservation genRes
GROUP BY Year(genRes.gen_res_date)
ORDER BY Year(genRes.gen_res_date);

/*Old data*/
/*General turistas por años*/
select Anno, adults + kids as tourist from
  (select YEAR(creation_date) as Anno, sum(adults) as adults, sum(children) as kids
   from old_payment
   group by YEAR(creation_date)
   order by YEAR(creation_date)) T;

/*General número de noches reservadas por habitaciones por años*/
select YEAR(creation_date) as Anno, sum(nights) as nights
from old_payment
group by YEAR(creation_date)
order by YEAR(creation_date);

/*General facturacion por años*/
select YEAR(creation_date) as Anno, sum(pay_at_accommodation) as payment
from old_payment
group by YEAR(creation_date)
order by YEAR(creation_date);

/*General total de reservaciones por años (bookings)*/
select YEAR(creation_date) as Anno, count(id) as totals
from old_payment
group by YEAR(creation_date)
order by YEAR(creation_date);

/*General total de solicitudes por casas por años*/
select YEAR(creation_date) as Anno, count(id) as totals
from old_reservation
group by YEAR(creation_date)
order by YEAR(creation_date);

