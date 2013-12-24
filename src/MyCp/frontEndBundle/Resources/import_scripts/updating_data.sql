update room
set room_type = "Habitación individual"
where room_type = 1;

update room
set room_type = "Habitación doble"
where room_type = 2;

update room
set room_type = "Habitación doble (Dos camas)"
where room_type = 3;

update room
set room_type = "Habitación Triple"
where room_type = 4;

update room
set room_bathroom = "Interior privado"
where room_bathroom = 0;

update room
set room_bathroom = "Exterior privado"
where room_bathroom = 1;

update room
set room_bathroom = "Compartido"
where room_bathroom = 2;

update room
set room_climate = "Ventilador"
where room_climate = 1;

update room
set room_climate = "Aire acondicionado"
where room_climate = 2;

update room
set room_audiovisual = "TV"
where room_audiovisual = 0;

update room
set room_audiovisual = "TV+DVD / Video"
where room_audiovisual = 1;

update room
set room_audiovisual = "TV cable"
where room_audiovisual = 2;