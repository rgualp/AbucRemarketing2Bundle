﻿/*
Inserting default metatags
Author: Yanet Morales Ramirez
*/

insert into `metatag` (meta_section, meta_title, meta_parent)
VALUES (1, "General",NULL),
(2, "Portada", 1),
(3, "Destinos", 1),
(4, "Alojamientos", 1),
(5, "MyCasaTrip", 1),
(6, "Favoritos", 1),
(7, "Cómo Funciona", 1),
(8, "Listados de Alojamientos", 4),
(9, "Últimos Alojamientos Añadidos", 8),
(10, "Alojamientos Económicos", 8),
(11, "Alojamientos Rango Medio", 8),
(12, "Alojamientos Premium", 8),
(13, "Ciudades Más Visitadas", 3),
(14, "Quiénes Somos", 1),
(15, "Contáctenos", 1),
(16, "Preguntas Frecuentes", 1),
(17, "Términos Legales", 1),
(18, "Seguridad y Privacidad", 1),
(19, "Mapa del Sitio", 1);

/*
Current metaLang have to be associated with Genera metatag
*/
update metalang
   set meta_tag = (select max(m.meta_id) FROM metatag m where m.meta_section = 1);