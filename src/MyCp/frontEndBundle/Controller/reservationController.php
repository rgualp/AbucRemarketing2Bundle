<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\generalReservation;

class reservationController extends Controller {

        public function clearAction(Request $request)
        {
            $request->getSession()->remove('services_pre_reservation'); exit();
            return $this->redirect($this->generateUrl('frontend_review_reservation'));
        }

        public function add_to_cartAction($id_ownership,Request $request)
        {
            $em = $this->getDoctrine()->getEntityManager();
            $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
            if(!$request->get('data_reservation'))
                throw $this->createNotFoundException();
            $data=$request->get('data_reservation');

            $data= explode('/',$data);

            $from_date=$data[0];
            $to_date=$data[1];
            $ids_rooms=$data[2];
            $count_guests=$data[3];
            $count_kids=$data[4];
            $array_ids_rooms=explode('&',$ids_rooms);
            array_shift($array_ids_rooms);
            $array_count_guests=explode('&',$count_guests);
            array_shift($array_count_guests);
            $array_count_kids=explode('&',$count_kids);
            array_shift($array_count_kids);

            $reservation_date_from=$from_date;
            $reservation_date_from=explode('&',$reservation_date_from);
            $start_timestamp = mktime(0,0,0,$reservation_date_from[1],$reservation_date_from[0],$reservation_date_from[2]);

            $reservation_date_to=$to_date;
            $reservation_date_to=explode('&',$reservation_date_to);
            $end_timestamp = mktime(0,0,0,$reservation_date_to[1],$reservation_date_to[0],$reservation_date_to[2]);

            $services=array();
            if($request->getSession()->get('services_pre_reservation'))
                $services=$request->getSession()->get('services_pre_reservation');

            for($a=0; $a < count($array_ids_rooms); $a++)
            {
                $insert=1;
                foreach($services as $serv)
                {
                    if(isset($array_count_guests[$a]) && isset($array_count_kids[$a]) && $serv['from_date']==$start_timestamp && $serv['to_date']==$end_timestamp &&
                        $serv['room']==$array_ids_rooms[$a] && $serv['guests']==$array_count_guests[$a] &&
                        $serv['kids']==$array_count_kids[$a] && $serv['ownership']=$id_ownership)
                    {
                        $insert=0;
                    }

                }
                if($insert==1)
                {
                    $room = $em->getRepository('mycpBundle:room')->find($array_ids_rooms[$a]);
                    $service['from_date']=$start_timestamp;
                    $service['to_date']=$end_timestamp;
                    $service['room']=$array_ids_rooms[$a];
                    if(isset($array_count_kids[$a]))
                        $service['guests']=$array_count_guests[$a];
                    else
                        $service['guests']=1;
                    if(isset($array_count_kids[$a]))
                        $service['kids']=$array_count_kids[$a];
                    else
                        $service['kids']=0;
                    $service['ownership_name']=$ownership->getOwnName();
                    $service['ownership_mun']=$ownership->getOwnAddressMunicipality()->getMunName();
                    $service['room_type']=$room->getRoomType();
                    $service['room_price_top']=$room->getRoomPriceUpFrom();
                    $service['room_price_down']=$room->getRoomPriceDownFrom();
                    array_push($services,$service);
                }

            }

            $request->getSession()->set('services_pre_reservation',$services);
            $request->getSession()->set('services_pre_reservation_last_own',$id_ownership);
            return $this->redirect($this->generateUrl('frontend_review_reservation'));
        }

        public function reviewAction(Request $request)
        {
            $em = $this->getDoctrine()->getEntityManager();
            $services=$request->getSession()->get('services_pre_reservation');
            $services=array();
            if($request->getSession()->get('services_pre_reservation'))
                $services=$request->getSession()->get('services_pre_reservation');

            $min_date=0;
            $max_date=0;

            if(isset($services[0]))
                $min_date=$services[0]['from_date'];

            if(isset($services[0]))
                $max_date=$services[0]['to_date'];


            foreach($services as $serv)
            {
                if($serv['from_date'] < $min_date)
                    $min_date=$serv['from_date'];
                if($serv['to_date'] > $max_date)
                    $max_date=$serv['to_date'];


            }

            $service_time= $this->get('Time');
            $array_dates=$service_time->dates_between($min_date,$max_date);
            $array_dates_string=array();
            $array_season=array();
            foreach($array_dates as $date)
            {
                array_push($array_dates_string ,\date('d/m/Y', $date));
                $season = $service_time->season_by_date($date);
                array_push($array_season,$season);
            }

            $last_own=$request->getSession()->get('services_pre_reservation_last_own');
            if($last_own)
                $ownership = $em->getRepository('mycpBundle:ownership')->find($last_own);
            else
                $ownership=0;
            return $this->render('frontEndBundle:reservation:reviewReservation.html.twig', array(
                'ownership'=>$ownership,
                'dates_string'=>$array_dates_string,
                'dates_timestamp'=>$array_dates,
                'services'=>$services,
                'array_season'=>$array_season
            ));
        }

       /* public function confirm_redirectAction($id_ownership,Request $request)
        {
            $data=$request->get('data_reservation');
            $secure=$this->get('secure');
            $encode_data=$secure->encode_string($data);
            return $this->redirect($this->generateUrl('frontend_review_confirm_reservation',array(
                'id_ownership'=>$id_ownership,
                'data'=>$encode_data
            )));
        }*/

       /* public function reviewAction($id_ownership,$data,Request $request)
        {

            $secure=$this->get('secure');
            $decode_data=$secure->decode_string($data);


            $url_string_string=$decode_data;
            $url_string= explode('/',$url_string_string);

            $em = $this->getDoctrine()->getEntityManager();
            $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);

            $from_date=$url_string[0];
            $to_date=$url_string[1];
            $ids_rooms=$url_string[2];
            $count_guests=$url_string[3];
            $count_kids=$url_string[4];
            $rooms_price=$url_string[5];
            $total_price=$url_string[6];

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
            $array_prices_day=array();
            foreach($array_rooms as $room)
            {
                $array_temp=array();
                foreach($array_dates as $date)
                {
                    $season=$service_time->season_by_date($date);
                    if($season=='down')
                    {
                        array_push($array_temp,$room->getRoomPriceDownTo());
                    }
                    else
                    {
                        array_push($array_temp,$room->getRoomPriceUpTo());
                    }
                }
                array_push($array_prices_day,$array_temp);

            }
            //var_dump($array_rooms);
            //exit();

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
                'total_price'=>$total_price,
                'url_string'=>$url_string_string,
                'array_price_day'=>$array_prices_day,
            ));

        }*/



        public function review_confirmAction($id_ownership,$data,Request $request)
        {
            $secure=$this->get('secure');
            $decode_data=$secure->decode_string($data);

            $url_string_string=$decode_data;
            $url_string=$decode_data;
            $url_string= explode('/',$url_string);
            $from_date=$url_string[0];
            $to_date=$url_string[1];
            $ids_rooms=$url_string[2];
            $count_guests=$url_string[3];
            $count_kids=$url_string[4];
            $rooms_price=$url_string[5];
            $total_price=$url_string[6];


            $em = $this->getDoctrine()->getEntityManager();            

            $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);

           /* $array_ids_rooms=explode('&amp;',$ids_rooms);
            if(count($array_ids_rooms)==1)
            {
                $array_ids_rooms=explode('&',$ids_rooms);
            }
            array_shift($array_ids_rooms);

            $array_guests=explode('&amp;',$count_guests);
            if(count($array_guests)==1)
            {
                $array_guests=explode('&',$count_guests);
            }
            array_shift($array_guests);

            $array_kids=explode('&amp;',$count_kids);
            if(count($array_kids)==1)
            {
                $array_kids=explode('&',$count_kids);
            }
            array_shift($array_kids);

            $array_date_from=explode('&amp;',$from_date);
            if(count($array_date_from)==1)
            {
                $array_date_from=explode('&',$from_date);
            }
            //var_dump($array_date_from); exit();
            $date_from_db=$array_date_from[2].'-'.$array_date_from[1].'-'.$array_date_from[0];

            $array_date_to=explode('&amp;',$to_date);
            if(count($array_date_to)==1)
            {
                $array_date_to=explode('&',$to_date);
            }


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
                'La consulta de disponibilidad de sus habitaciones ha sido enviada al equipo de reservación','');
*/
            /*
             * Hallando otros ownerships en el mismo destino
             */
            $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);
            $owns_in_destination = $em->getRepository("mycpBundle:destination")->ownsership_nearby_destination($ownership->getOwnAddressMunicipality()->getMunId(), $ownership->getOwnAddressProvince()->getProvId(), 4, $id_ownership);
            $owns_in_destination_photo = $em->getRepository("mycpBundle:ownership")->get_photos_array($owns_in_destination);
            $owns_in_destination_favorities = $em->getRepository('mycpBundle:favorite')->is_in_favorite_array($owns_in_destination, true, $user_ids['user_id'], $user_ids['session_id']);
            
            $locale = $this->get('translator')->getLocale();
            $destinations = $em->getRepository('mycpBundle:destination')->destination_filter(null,$ownership->getOwnAddressProvince()->getProvId(),null,$ownership->getOwnAddressMunicipality()->getMunId(),2);
            $destinations_photos = $em->getRepository('mycpBundle:destination')->get_destination_photos($destinations);
            $destinations_descriptions = $em->getRepository('mycpBundle:destination')->get_destination_description($destinations, $locale);
            $destinations_favorities  = $em->getRepository('mycpBundle:favorite')->is_in_favorite_array($destinations, false, $user_ids['user_id'], $user_ids['session_id']);       
            $destinations_count = $em->getRepository('mycpBundle:destination')->get_destination_owns_statistics($destinations); 
            
            return $this->render('frontEndBundle:reservation:confirmReservation.html.twig', array(
                "owns_in_destination" => $owns_in_destination,
                "owns_in_destination_photos" => $owns_in_destination_photo,
                "owns_in_destination_favorities" => $owns_in_destination_favorities,
                "owns_in_destination_total" => count($em->getRepository("mycpBundle:destination")->ownsership_nearby_destination($ownership->getOwnAddressMunicipality()->getMunId(), $ownership->getOwnAddressProvince()->getProvId())),
                "other_destinations" => $destinations,
                "other_destinations_photos" => $destinations_photos,
                "other_destinations_descriptions" => $destinations_descriptions,
                "other_destinations_favorities" => $destinations_favorities,
                "other_destinations_count" => $destinations_count
            ));

        }

}
