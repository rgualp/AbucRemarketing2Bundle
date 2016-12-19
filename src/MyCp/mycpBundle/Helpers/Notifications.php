<?php

/**
 * Description of Reservation
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;

class Notifications  {

    public static function sendNotifications($url, $param, $access_token)
    {
        $headr[] = 'token: '.$access_token;
        //open connection
        $curl = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($curl,CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($param));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headr);
        $remote_server_output = curl_exec ($curl);
        // cerramos la sesión cURL
        curl_close ($curl);
    }

}

?>
