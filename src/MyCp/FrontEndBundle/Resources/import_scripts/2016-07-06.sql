/*
Initializing  ownershipdata table
Author: Yanet Morales Ramirez
*/

insert into ownershipdata(accommodation, touristClients, activeRooms, publishedComments, reservedRooms, principalPhoto)
  select o.own_id,0,
    IFNULL((select count(r.room_id) from room r where r.room_ownership = o.own_id and r.room_active = 1 group by r.room_ownership), 0) as activeRooms,
    IFNULL((select count(c.com_id) from `comment` c where c.com_public = 1 and c.com_ownership = o.own_id group by c.com_ownership), 0) as publishedComments,
    IFNULL((select count(owres.own_res_id) from `ownershipreservation` owres
      join generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
    where gres.gen_res_own_id = o.own_id and owres.own_res_status = 5
    group by gres.gen_res_own_id
           ), 0) as reservedRooms,
    (select opho.`own_pho_id` from `ownershipphoto` opho
      join photo pho on pho.pho_id = opho.own_pho_pho_id
    where opho.own_pho_own_id = o.own_id
     order by pho.pho_order ASC
     limit 1
    ) as principalPhoto
  from ownership o
  order by o.own_id ASC;
