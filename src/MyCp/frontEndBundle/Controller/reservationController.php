<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\generalReservation;

class reservationController extends Controller {
    
        public function reviewAction($id_ownership,$from_date,$to_date,$ids_rooms,$count_guests, $total_price,Request $request)
        {

            $em = $this->getDoctrine()->getEntityManager();
            $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);

            $array_ids_rooms=explode('&',$ids_rooms);
            array_shift($array_ids_rooms);
            $array_count_guests=explode('&',$count_guests);
            array_shift($array_count_guests);

            $array_rooms=array();
            foreach($array_ids_rooms as $id_room)
            {
                $room=$em->getRepository('mycpBundle:room')->find($id_room);
                $array_rooms[count($array_rooms)]=$room;
            }

            $reservation_date_from=$from_date;
            $reservation_date_from=explode('&',$reservation_date_from);
            $start_timestamp = mktime(0,0,0,$reservation_date_from[1],$reservation_date_from[0],$reservation_date_from[2]);

            $reservation_date_to=$to_date;
            $reservation_date_to=explode('&',$reservation_date_to);
            $end_timestamp = mktime(0,0,0,$reservation_date_to[1],$reservation_date_to[0],$reservation_date_to[2]);

            $service_time= $this->get('Time');
            $array_dates=$service_time->dates_between($start_timestamp,$end_timestamp);

            return $this->render('frontEndBundle:reservation:reviewReservation.html.twig',array(
                'ownership'=>$ownership,
                'from_date'=>$from_date,
                'to_date'=>$to_date,
                'array_dates'=>$array_dates,
                'array_rooms'=>$array_rooms,
                'guests'=>$array_count_guests,
                'guests_string'=>$count_guests,
                'rooms_string'=>$ids_rooms,
                'total_price'=>$total_price
            ));
        }

    public function review_confirmAction($id_ownership,$from_date,$to_date,$ids_rooms,$count_guests, $total_price,Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);

        $array_ids_rooms=explode('&',$ids_rooms);
        array_shift($array_ids_rooms);

        $array_guests=explode('&',$count_guests);
        array_shift($array_guests);

        $array_date_from=explode('&',$from_date);
        $date_from_db=$array_date_from[2].'-'.$array_date_from[1].'-'.$array_date_from[0];

        $array_date_to=explode('&',$to_date);
        $date_to_db=$array_date_to[2].'-'.$array_date_to[1].'-'.$array_date_to[0];

        $user = $this->get('security.context')->getToken()->getUser();
        $general_reservation=new generalReservation();
        $general_reservation->setGenResUserId($user);
        $em->persist($general_reservation);
        $em->flush();

        for($i=0; $i < count($array_ids_rooms); $i++)
        {
            $room=$em->getRepository('mycpBundle:room')->find($array_ids_rooms[$i]);
            $reservation=new ownershipReservation();
            $reservation->setOwnResCommissionPercent(10);
            $reservation->setOwnResCountAdults($array_guests[$i]);
            $reservation->setOwnResCountChildrens(0);
            $reservation->setOwnResNightPrice(0);
            $reservation->setOwnResOwnId($ownership);
            $reservation->setOwnResReservationDate(new \DateTime(date('Y-m-d')));
            $reservation->setOwnResReservationStatus(0);
            $reservation->setOwnResReservationStatusDate(new \DateTime(date('Y-m-d')));
            $reservation->setOwnResReservationFromDate(new \DateTime($date_from_db));
            $reservation->setOwnResReservationToDate(new \DateTime($date_to_db));
            $reservation->setOwnResSelectedRoom($array_ids_rooms[$i]);
            $reservation->setOwnResGenResId($general_reservation);
            $em->persist($reservation);
        }
        $em->flush();

        $service_email= $this->get('Email');
        $service_email->send_email(
            'Consulta de disponibilidad enviada',
            'noreply@mycasaparticular.com',
             $user->getUserEmail(),
            'Consulta de disponibilidad enviada',
            'La consulta de disponibilidad de sus habitaciones ha sido enviada al equipo de reservación','');

        return $this->render('frontEndBundle:reservation:confirmReservation.html.twig');
    }

}
