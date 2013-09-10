<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\generalReservation;

class reservationController extends Controller {
    
        public function reviewAction($id_ownership,$from_date,$to_date,$ids_rooms,$count_guests,$count_kids, $rooms_price,$total_price,Request $request)
        {

            $em = $this->getDoctrine()->getEntityManager();
            $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);

            $array_ids_rooms=explode('&',$ids_rooms);
            array_shift($array_ids_rooms);
            $array_rooms_price=explode('&',$rooms_price);
            array_shift($array_rooms_price);
            $array_count_guests=explode('&',$count_guests);
            array_shift($array_count_guests);
            $array_count_kids=explode('&',$count_kids);
            array_shift($array_count_kids);

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
                'kids'=>$array_count_kids,
                'guests_string'=>$count_guests,
                'kids_string'=>$count_kids,
                'rooms_string'=>$ids_rooms,
                'rooms_price'=>$array_rooms_price,
                'rooms_price_string'=>$rooms_price,
                'total_price'=>$total_price
            ));
        }

    public function review_confirmAction($id_ownership,$from_date,$to_date,$ids_rooms,$count_guests,$count_kids, $total_price,Request $request)
    {

        $em = $this->getDoctrine()->getEntityManager();

        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);

        $array_ids_rooms=explode('&',$ids_rooms);
        array_shift($array_ids_rooms);

        $array_guests=explode('&',$count_guests);
        array_shift($array_guests);

        $array_kids=explode('&',$count_kids);
        array_shift($array_kids);

        $array_date_from=explode('&',$from_date);
        $date_from_db=$array_date_from[2].'-'.$array_date_from[1].'-'.$array_date_from[0];

        $array_date_to=explode('&',$to_date);
        $date_to_db=$array_date_to[2].'-'.$array_date_to[1].'-'.$array_date_to[0];
        $user = $this->get('security.context')->getToken()->getUser();
        $general_reservation=new generalReservation();
        $general_reservation->setGenResUserId($user);
        $general_reservation->setGenResDate(new \DateTime(date('Y-m-d')));
        $general_reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
        $general_reservation->setGenResStatus(0);
        $general_reservation->setGenResTotalPriceInSite($total_price);
        $general_reservation->setGenResFromDate(new \DateTime($date_from_db));
        $general_reservation->setGenResToDate(new \DateTime($date_to_db));
        $general_reservation->setGenResOwnId($ownership);
        $general_reservation->setGenResSaved(0);
        $em->persist($general_reservation);
       $em->flush();

        for($i=0; $i < count($array_ids_rooms); $i++)
        {
            $room=$em->getRepository('mycpBundle:room')->find($array_ids_rooms[$i]);
            $reservation=new ownershipReservation();
            $reservation->setOwnResCountAdults($array_guests[$i]);
            $reservation->setOwnResCountChildrens($array_kids[$i]);
            $reservation->setOwnResNightPrice(0);
            $reservation->setOwnResStatus(0);
            $reservation->setOwnResReservationFromDate(new \DateTime($date_from_db));
            $reservation->setOwnResReservationToDate(new \DateTime($date_to_db));
            $reservation->setOwnResSelectedRoomId($array_ids_rooms[$i]);
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
