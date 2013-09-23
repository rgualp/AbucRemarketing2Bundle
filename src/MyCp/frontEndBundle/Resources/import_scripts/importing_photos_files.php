<?php

/*
 * Importing Photo Files from old website MyCasaParticular.com
 */
//Settings to images paths
$old_website_path_to_images = "http://www.mycasaparticular.com/images/";
$new_website_path_to_images = "http://localhost/mycp2.0/web/uploads/";
$old_website_path_to_albums_photos = "";
$old_website_path_to_destinations_photos = "";
$old_website_path_to_ownerships_photos = "";

//Old database settings
$db_hostname = "localhost";
$db_username = "root";
$db_password = "";
$db_database = "old_mypaladar_mycp";


$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());
mysql_select_db($db_database)
or die("Unable to select database: " . mysql_error());


import_photos("SELECT fa_img FROM fotografias_albumes",
              $old_website_path_to_images.$old_website_path_to_albums_photos,
              $new_website_path_to_images."albumImages/");

import_photos("SELECT fd_img FROM fotografias_destinos",
              $old_website_path_to_images.$old_website_path_to_destinations_photos,
              $new_website_path_to_images."destinationImages/");

import_photos("SELECT fot_img FROM fotografias",
              $old_website_path_to_images.$old_website_path_to_ownerships_photos,
              $new_website_path_to_images."ownershipImages/");

function import_photos($query,$from_path, $to_path)
{    
    $result = mysql_query($query);
    
    if (!$result) die ("Database access failed: " . mysql_error());
    
    $rows = mysql_num_rows($result);
    
    for ($j = 0 ; $j < $rows ; ++$j)
    {
        $row = mysql_fetch_row($result); 
        copy($from_path.$row[0], $to_path.$row[0]);
    }
}

?>
