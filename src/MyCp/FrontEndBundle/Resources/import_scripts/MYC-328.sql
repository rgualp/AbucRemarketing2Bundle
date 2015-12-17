set @today = '2015-12-17';

insert into `unavailabilitydetails` (room_id, ud_sync_st, ud_from_date, ud_to_date, ud_reason)
  select ownRes.own_res_selected_room_id,0,
    ownRes.own_res_reservation_from_date,
    ownRes.own_res_reservation_to_date,
    concat("Generada automaticamente por ser no disponible la reserva CAS.",genRes.gen_res_id)
  from ownershipreservation ownRes
    join generalreservation genRes
      on ownRes.own_res_gen_res_id = genRes.gen_res_id
  where ownRes.own_res_status = 3
        and ((ownRes.own_res_reservation_from_date <= @today
              and ownRes.own_res_reservation_to_date >= @today)
             or ownRes.own_res_reservation_from_date > @today)
  order by ownRes.own_res_reservation_from_date DESC;