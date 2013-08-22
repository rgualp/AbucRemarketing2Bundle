<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class reservationController extends Controller {
    
        public function reviewAction(Request $request)
        {
            $em = $this->getDoctrine()->getEntityManager();
            $ownership = $em->getRepository('mycpBundle:ownership')->find($request->get('ownership'));
            $ids_rooms=$request->get('ids_rooms');
            $ids_rooms=explode(',',$ids_rooms);
            array_shift($ids_rooms);
            $count_guests=$request->get('guests');
            $count_guests=explode(',',$count_guests);
            array_shift($count_guests);

            $array_rooms=array();
            foreach($ids_rooms as $id_room)
            {
                $room=$em->getRepository('mycpBundle:room')->find($id_room);
                $array_rooms[count($array_rooms)]=$room;
            }

            $reservation_date_from=$request->get('from_date');
            $reservation_date_from=explode('/',$reservation_date_from);
            $start_timestamp = mktime(0,0,0,$reservation_date_from[1],$reservation_date_from[0],$reservation_date_from[2]);

            $reservation_date_to=$request->get('to_date');
            $reservation_date_to=explode('/',$reservation_date_to);
            $end_timestamp = mktime(0,0,0,$reservation_date_to[1],$reservation_date_to[0],$reservation_date_to[2]);

            $array_dates=$this->dates_between($start_timestamp,$end_timestamp);

            return $this->render('frontEndBundle:reservation:reviewReservation.html.twig',array(
                'ownership'=>$ownership,
                'from_date'=>$request->get('from_date'),
                'to_date'=>$request->get('to_date'),
                'array_dates'=>$array_dates,
                'array_rooms'=>$array_rooms,
                'guests'=>$count_guests,
                'total_price'=>$request->get('total_price_submit')
            ));
        }

    public static function dates_between($startdate, $enddate, $format=null){

        (is_int($startdate)) ? 1 : $startdate = strtotime($startdate);
        (is_int($enddate)) ? 1 : $enddate = strtotime($enddate);

        if($startdate > $enddate){
            return false; //The end date is before start date
        }

        while($startdate < $enddate){
            $arr[] = ($format) ? date($format, $startdate) : $startdate;
            $startdate += 86400;
        }
        $arr[] = ($format) ? date($format, $enddate) : $enddate;

        return $arr;
    }
}
