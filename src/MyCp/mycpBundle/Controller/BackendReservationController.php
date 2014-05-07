<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\payment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\log;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Form\reservationType;



class BackendReservationController extends Controller
{
    public function new_offerAction($id_tourist,$id_reservation){
        $em = $this->getDoctrine()->getManager();
        $user= $em->getRepository('mycpBundle:user')->find($id_tourist);
        return $this->render('mycpBundle:reservation:new_offer.html.twig',array(
            'id_reservation'=>$id_reservation,
            'user'=>$user
        ));
    }

    public function list_reservationsAction($items_per_page, Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $page=1;
        $filter_date_reserve=$request->get('filter_date_reserve');
        $filter_offer_number=$request->get('filter_offer_number');
        $filter_reference=$request->get('filter_reference');
        $filter_date_from=$request->get('filter_date_from');
        $filter_date_to=$request->get('filter_date_to');
        $filter_booking_number=$request->get('filter_booking_number');
        $price=0;
        $sort_by=$request->get('sort_by');
        if($request->getMethod()=='POST' && $filter_date_reserve=='null' && $filter_offer_number=='null' && $filter_reference=='null'&&
            $filter_date_from=='null' && $filter_date_to=='null' && $sort_by=='null' && $filter_booking_number=='null'
        )
        {
            $message='Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local',$message);
            return $this->redirect($this->generateUrl('mycp_list_reservations'));
        }

        if($filter_date_reserve=='null') $filter_date_reserve='';
        if($filter_offer_number=='null') $filter_offer_number='';
        if($filter_booking_number=='null') $filter_booking_number='';
        if($filter_reference=='null') $filter_reference='';
        if($filter_date_from=='null') $filter_date_from='';
        if($filter_date_to=='null') $filter_date_to='';
        if($sort_by=='null') $sort_by='';

        if(isset($_GET['page']))$page=$_GET['page'];
        $filter_date_reserve=str_replace('_','/',$filter_date_reserve);
        $filter_date_from=str_replace('_','/',$filter_date_from);
        $filter_date_to=str_replace('_','/',$filter_date_to);

        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $reservations= $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
            ->get_all_reservations($filter_date_reserve, $filter_offer_number, $filter_reference,
            $filter_date_from,$filter_date_to,$sort_by,$filter_booking_number))->getResult();
        $filter_date_reserve_twig=str_replace('/','_',$filter_date_reserve);
        $filter_date_from_twig=str_replace('/','_',$filter_date_from);
        $filter_date_to_twig=str_replace('/','_',$filter_date_to);
        $service_log= $this->get('log');
        $service_log->save_log('Visit',7);
        $total_nights=array();
        $service_time=$this->get('time');
        foreach($reservations as $res)
        {
            $owns_res=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$res[0]['gen_res_id']));
            $temp_total_nights=0;
            foreach($owns_res as $own)
            {
                $array_dates= $service_time->dates_between($own->getOwnResReservationFromDate()->getTimestamp(),$own->getOwnResReservationToDate()->getTimestamp());
                $temp_total_nights+=count($array_dates)-1;
            }
            array_push($total_nights,$temp_total_nights);
        }
        return $this->render('mycpBundle:reservation:list.html.twig',array(
            'total_nights'=>$total_nights,
            'reservations'=>$reservations,
            'items_per_page'=>$items_per_page,
            'current_page'=>$page,
            'total_items'=>$paginator->getTotalItems(),
            'filter_date_reserve'=>$filter_date_reserve,
            'filter_offer_number'=>$filter_offer_number,
            'filter_booking_number'=>$filter_booking_number,
            'filter_reference'=>$filter_reference,
            'filter_date_from'=>$filter_date_from,
            'filter_date_to'=>$filter_date_to,
            'sort_by'=>$sort_by,
            'filter_date_reserve_twig'=>$filter_date_reserve_twig,
            'filter_date_from_twig'=>$filter_date_from_twig,
            'filter_date_to_twig'=>$filter_date_to_twig
        ));
    }

    public function list_reservations_bookingAction($items_per_page, Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();

        $filter_booking_number=$request->get('filter_booking_number');
        $filter_date_booking=$request->get('filter_date_booking');
        $filter_user_booking=$request->get('filter_user_booking');

        if($request->getMethod()=='POST' && $filter_booking_number=='null' && $filter_date_booking=='null' && $filter_user_booking=='null')
        {
            $message='Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local',$message);
            return $this->redirect($this->generateUrl('mycp_list_reservations_booking'));
        }

        if($filter_booking_number=='null') $filter_booking_number='';
        if($filter_date_booking=='null') $filter_date_booking='';
        if($filter_user_booking=='null') $filter_user_booking='';

        $page=1;
        if(isset($_GET['page']))$page=$_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $bookings= $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
            ->get_all_bookings($filter_booking_number,$filter_date_booking,$filter_user_booking))->getResult();
        $service_log= $this->get('log');
        $service_log->save_log('Visit',7);

        $filter_date_booking=str_replace('_','/',$filter_date_booking);

        return $this->render('mycpBundle:reservation:list_booking.html.twig',array(
            "bookings"=>$bookings,
            'items_per_page'=>$items_per_page,
            'current_page'=>$page,
            'filter_booking_number'=>$filter_booking_number,
            'filter_date_booking'=>$filter_date_booking,
            'filter_user_booking'=>$filter_user_booking,
            'total_items'=>$paginator->getTotalItems(),
        ));
    }

    public function details_bookingAction($id_booking)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $em = $this->getDoctrine()->getManager();
        $booking=$em->getRepository('mycpBundle:booking')->find($id_booking);
        $user=$em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user'=>$booking->getBookingUserId()));
        $reservations=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_reservation_booking'=>$id_booking,'own_res_status'=>5));
        return $this->render('mycpBundle:reservation:bookingDetails.html.twig',array(
            'user'=>$user,
            'reservations'=>$reservations,
            'booking'=>$id_booking
        ));
    }

    public function list_reservations_userAction($items_per_page, Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $page=1;
        $filter_date_reserve=$request->get('filter_date_reserve');
        $filter_offer_number=$request->get('filter_offer_number');
        $filter_booking_number=$request->get('filter_booking_number');
        $filter_reference=$request->get('filter_reference');
        $filter_date_from=$request->get('filter_date_from');
        $filter_date_to=$request->get('filter_date_to');

        $sort_by=$request->get('sort_by');
        if($request->getMethod()=='POST' && $filter_date_reserve=='null' && $filter_offer_number=='null' && $filter_reference=='null'&&
            $filter_date_from=='null' && $filter_date_to=='null' && $sort_by=='null' && $filter_booking_number=='null'
        )
        {
            $message='Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local',$message);
            return $this->redirect($this->generateUrl('mycp_list_reservations_user'));
        }
        if($filter_date_reserve=='null') $filter_date_reserve='';
        if($filter_offer_number=='null') $filter_offer_number='';
        if($filter_reference=='null') $filter_reference='';
        if($filter_date_from=='null') $filter_date_from='';
        if($filter_booking_number=='null') $filter_booking_number='';
        if($filter_date_to=='null') $filter_date_to='';
        if($sort_by=='null') $sort_by='';

        if(isset($_GET['page']))$page=$_GET['page'];
        $filter_date_reserve=str_replace('_','/',$filter_date_reserve);
        $filter_date_from=str_replace('_','/',$filter_date_from);
        $filter_date_to=str_replace('_','/',$filter_date_to);

        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $reservations= $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
            ->get_all_reservations($filter_date_reserve, $filter_offer_number, $filter_reference,
            $filter_date_from,$filter_date_to,$sort_by,$filter_booking_number))->getResult();

        $filter_date_reserve_twig=str_replace('/','_',$filter_date_reserve);
        $filter_date_from_twig=str_replace('/','_',$filter_date_from);
        $filter_date_to_twig=str_replace('/','_',$filter_date_to);

        $service_log= $this->get('log');
        $service_log->save_log('Visit',7);

        $currencies=array();
        $languages=array();
        $service_time=$this->get('time');
        $total_nights=array();
        foreach($reservations as $reservation)
        {
            $user_tourist= $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user'=>$reservation[0]['gen_res_user_id']['user_id']));
            if($user_tourist[0]->getUserTouristCurrency())
            array_push($currencies,$user_tourist[0]->getUserTouristCurrency()->getCurrCode());

            if($user_tourist[0]->getUserTouristLanguage())
            array_push($languages,$user_tourist[0]->getUserTouristLanguage()->getLangName());

            $owns_res=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$reservation[0]['gen_res_id']));
            $total_nights_temp=0;
            foreach($owns_res as $own)
            {
                $array_dates= $service_time->dates_between($own->getOwnResReservationFromDate()->getTimestamp(),$own->getOwnResReservationToDate()->getTimestamp());
                $total_nights_temp+=count($array_dates)-1;
            }
            array_push($total_nights,$total_nights_temp);
        }


        return $this->render('mycpBundle:reservation:list_client.html.twig',array(
            'total_nights'=>$total_nights,
            'languages'=>$languages,
            'currencies'=>$currencies,
            'reservations'=>$reservations,
            'items_per_page'=>$items_per_page,
            'current_page'=>$page,
            'total_items'=>$paginator->getTotalItems(),
            'filter_date_reserve'=>$filter_date_reserve,
            'filter_offer_number'=>$filter_offer_number,
            'filter_reference'=>$filter_reference,
            'filter_booking_number'=>$filter_booking_number,
            'filter_date_from'=>$filter_date_from,
            'filter_date_to'=>$filter_date_to,
            'sort_by'=>$sort_by,
            'filter_date_reserve_twig'=>$filter_date_reserve_twig,
            'filter_date_from_twig'=>$filter_date_from_twig,
            'filter_date_to_twig'=>$filter_date_to_twig
        ));
    }

    public function details_client_reservationAction($id_client,Request $request)
    {

        //$service_security= $this->get('Secure');
        //$service_security->verify_access();

        //$service_log= $this->get('log');
        //$service_log->save_log('Visit',7);

        $service_time= $this->get('time');


        $em = $this->getDoctrine()->getEntityManager();
        $client=$em->getRepository('mycpBundle:user')->find($id_client);
        $reservations=$em->getRepository('mycpBundle:generalReservation')->get_reservations_by_user($id_client);
        $price=0;
        $total_nights=array();

        if($request->getMethod()=='POST')
        {

            $post = $request->request->getIterator()->getArrayCopy();
            //var_dump($post); exit();
            foreach($reservations as $reservation)
            {
                $res_db=$em->getRepository('mycpBundle:generalReservation')->find($reservation[0]['gen_res_id']);
                $res_db->setGenResStatus($post['resume_status_res_'.$reservation[0]['gen_res_id']]);
                $em->persist($res_db);

                $own_reservations=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$reservation[0]['gen_res_id']));
                foreach($own_reservations as $own_reservation)
                {
                    if( isset($post['service_room_type_'.$own_reservation->getOwnResId()]))
                    {
                        $own_reservation->setOwnResRoomType($post['service_room_type_'.$own_reservation->getOwnResId()]);
                        $own_reservation->setOwnResCountAdults($post['service_room_count_adults_'.$own_reservation->getOwnResId()]);
                        $own_reservation->setOwnResCountChildrens($post['service_room_count_childrens_'.$own_reservation->getOwnResId()]);
                        $own_reservation->setOwnResNightPrice($post['service_room_price_'.$own_reservation->getOwnResId()]);
                        $own_reservation->setOwnResStatus($post['service_own_res_status_'.$own_reservation->getOwnResId()]);
                        $em->persist($own_reservation);
                    }
                }

            }
            $em->flush();
            $message='Reservas actualizadas satisfactoriamente.';

            /*$service_log= $this->get('log');
            $service_log->save_log('Create entity for '.$ownership->getOwnMcpCode(),7);*/

            $this->get('session')->getFlashBag()->add('message_ok',$message);
            return $this->redirect($this->generateUrl('mycp_details_client_reservation',array('id_client'=>$id_client)));

        }

        $service_time=$this->get('time');
        foreach($reservations as $reservation)
        {
            $temp_total_nights=0;
            $owns_res=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$reservation[0]['gen_res_id']));

            foreach($owns_res as $own)
            {
                $array_dates= $service_time->dates_between($own->getOwnResReservationFromDate()->getTimestamp(),$own->getOwnResReservationToDate()->getTimestamp());
                $temp_total_nights+=count($array_dates)-1;
            }
            array_push($total_nights,$temp_total_nights);
        }
        return $this->render('mycpBundle:reservation:reservationDetailsClient.html.twig',array(
            'total_nights'=>$total_nights,
            'reservations'=>$reservations,
            'client'=>$client,
            'errors'=>''
        ));
    }

    public function new_reservationAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $data=array();
        $em = $this->getDoctrine()->getEntityManager();
        $role=$em->getRepository('mycpBundle:role')->findBy(array('role_name'=>'ROLE_CLIENT_TOURIST'));
        $post=$request->get('mycp_mycpbundle_reservationtype');
        $ownerships= $em->getRepository('mycpBundle:ownership')->findAll();
        $rooms=$em->getRepository('mycpBundle:room')->findBy(array('room_ownership'=>$post['reservation_ownership']));
        $users=$em->getRepository('mycpBundle:user')->findAll();
        $data['ownerships']=$ownerships;
        $data['rooms']=$rooms;
        $data['users']=$users;

        $form = $this->createForm(new reservationType($data));

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->getRepository('mycpBundle:ownershipReservation')->new_reservation($post);
                $message='Reserva añadida satisfactoriamente.';
                $ownership= $em->getRepository('mycpBundle:ownership')->find($post['reservation_ownership']);

                $service_log= $this->get('log');
                $service_log->save_log('Create entity for '.$ownership->getOwnMcpCode(),7);

                $this->get('session')->getFlashBag()->add('message_ok',$message);
                return $this->redirect($this->generateUrl('mycp_list_reservations'));
            }
        }
        return $this->render('mycpBundle:reservation:new.html.twig',array('form'=>$form->createView(),'role'=>$role[0]));
    }

    public function details_reservation_partialAction($id_reservation)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $reservation=new generalReservation();
        $reservation=$em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$id_reservation));

        $service_time= $this->get('time');

        $user=$em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user'=>$reservation->getGenResUserId()));

        $rooms=array();
        $total_nights=array();
        foreach($ownership_reservations as $res)
        {
            array_push($rooms,$em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            $temp_total_nights=0;
            $array_dates= $service_time->dates_between($res->getOwnResReservationFromDate()->getTimestamp(),$res->getOwnResReservationToDate()->getTimestamp());
            $temp_total_nights+=count($array_dates)-1;

            array_push($total_nights,$temp_total_nights);
        }

        return $this->render('mycpBundle:reservation:reservationDetailsPartial.html.twig',array(
            'nights'=>$total_nights,
            'reservation'=>$reservation,
            'user'=>$user,
            'reservations'=>$ownership_reservations,
            'rooms'=>$rooms,
            'id_reservation'=>$id_reservation
        ));
    }

    public function details_reservationAction($id_reservation,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $reservation=new generalReservation();
        $reservation=$em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$id_reservation));
        $errors=array();

        $service_time= $this->get('time');
        $post = $request->request->getIterator()->getArrayCopy();
        $dates=$service_time->dates_between($reservation->getGenResFromDate()->format('Y-m-d'), $reservation->getGenResToDate()->format('Y-m-d'));
        $not_available=true;
        if($request->getMethod()=='POST')
        {
            $keys=array_keys($post);

            foreach($keys as $key)
            {
                if(strpos($key, 'service_room_price')!==false)
                {
                    if(empty($post[$key])  OR !is_numeric($post[$key]))
                    {
                        $errors[$key]=1;
                    }

                }
                if(strpos($key, 'service_own_res_status')!==false)
                {
                    if($post[$key] < 3)
                    {
                        $not_available=false;
                    }
                }
            }

            if(count($errors)==0)
            {

                $ownership_reservation=new ownershipReservation();
                $temp_price=0;
                foreach($ownership_reservations as $ownership_reservation)
                {
                    $temp_price+=$post['service_room_price_'.$ownership_reservation->getOwnResId()]*(count($dates)-1);
                    $ownership_reservation->setOwnResCountAdults($post['service_room_count_adults_'.$ownership_reservation->getOwnResId()]);
                    $ownership_reservation->setOwnResCountChildrens($post['service_room_count_childrens_'.$ownership_reservation->getOwnResId()]);
                    $ownership_reservation->setOwnResStatus($post['service_own_res_status_'.$ownership_reservation->getOwnResId()]);
                    $ownership_reservation->setOwnResRoomType($post['service_room_type_'.$ownership_reservation->getOwnResId()]);
                    $ownership_reservation->setOwnResNightPrice($post['service_room_price_'.$ownership_reservation->getOwnResId()]);

                    $start=explode('/',$post['date_from_'.$ownership_reservation->getOwnResId()]);
                    $end=explode('/',$post['date_to_'.$ownership_reservation->getOwnResId()]);
                    $start_timestamp = mktime(0, 0, 0, $start[1], $start[0], $start[2]);
                    $end_timestamp = mktime(0, 0, 0, $end[1], $end[0], $end[2]);

                    $ownership_reservation->setOwnResReservationFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
                    $ownership_reservation->setOwnResReservationToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));
                    $em->persist($ownership_reservation);
                }
                $message='Reserva actualizada satisfactoriamente.';
                $reservation->setGenResSaved(1);
                if($not_available == true)
                {
                    if($reservation->getGenResStatus()!=2)
                        $reservation->setGenResStatus(3);
                }
                else
                {
                    $reservation->setGenResStatus(1);
                }
                $em->persist($reservation);
                $em->flush();
                $service_log= $this->get('log');
                $service_log->save_log('Edit entity for CAS.'.$reservation->getGenResId(),7);

                $this->get('session')->getFlashBag()->add('message_ok',$message);

            }

        }

        $user=$em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user'=>$reservation->getGenResUserId()));
        $array_nights=array();
        $rooms=array();
        foreach($ownership_reservations as $res)
        {
            $dates_temp=$service_time->dates_between($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            array_push($rooms,$em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            array_push($array_nights,count($dates_temp)-1);

        }

        array_pop($dates);
        return $this->render('mycpBundle:reservation:reservationDetails.html.twig',array(
            'post'=>$post,
            'errors'=>$errors,
            'reservation'=>$reservation,
            'user'=>$user,
            'reservations'=>$ownership_reservations,
            'rooms'=>$rooms,
            'nights'=>$array_nights,
            'id_reservation'=>$id_reservation));
    }

    public function send_reservationAction($id_reservation)
    {
        $service_email= $this->get('Email');
        $service_time= $this->get('time');

        //send reserved reservations
        $em = $this->getDoctrine()->getManager();
        $own_reservations=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$id_reservation,'own_res_status'=>5));

        $total_price=0;
        if($own_reservations)
        {
            $general_reservation=$own_reservations[0]->getOwnResGenResId();
            $general_reservation->setGenResStatus(2);
            $general_reservation->setGenResStatusDate(new \DateTime());
            $em->persist($general_reservation);
            $user=$own_reservations[0]->getOwnResGenResId()->getGenResUserId();
            $user_tourist=$em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user'=>$user->getUserId()));

            foreach($own_reservations as $own_reservation)
            {
                $dates_temp=$service_time->dates_between($own_reservation->getOwnResReservationFromDate()->getTimestamp(), $own_reservation->getOwnResReservationToDate()->getTimestamp());
                $total_price+=$own_reservation->getOwnResNightPrice()*(count($dates_temp)-1);
            }

            $total_price=$total_price * $own_reservations[0]->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent() / 100;
            $prepay=($total_price + 10) * $user_tourist->getUserTouristCurrency()->getCurrCucChange();
            $booking= new booking();
            $booking->setBookingCurrency($user_tourist->getUserTouristCurrency());
            $booking->setBookingPrepay($prepay);
            $booking->setBookingUserDates($user_tourist->getUserTouristUser()->getUserUserName().", ".$user_tourist->getUserTouristUser()->getUserEmail());
            $booking->setBookingCancelProtection(0);
            $booking->setBookingUserId($user_tourist->getUserTouristUser()->getUserId());
            $em->persist($booking);

            $payment= new payment();
            $payment->setBooking($booking);
            $payment->setCreated(new \DateTime(date('Y-m-d')));
            $payment->setModified(new \DateTime(date('Y-m-d')));
            $payment->setCurrency($user_tourist->getUserTouristCurrency());
            $payment->setStatus(1);
            $payment->setPayedAmount($prepay);
            $em->persist($payment);

            $array_photos=array();
            $array_ownres_by_house=array();
            $service_time=$this->get('time');
            $array_houses_ids=array();
            $array_nigths=array();
            $array_houses=array();

            foreach($own_reservations as $own_reservation)
            {
                $own_reservation->setOwnResReservationBooking($booking);
                $em->persist($own_reservation);

                $photos=$em->getRepository('mycpBundle:ownership')->getPhotos($own_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId());
                array_push($array_photos,$photos);
                $array_dates= $service_time->dates_between($own_reservation->getOwnResReservationFromDate()->getTimestamp(),$own_reservation->getOwnResReservationToDate()->getTimestamp());
                array_push($array_nigths,count($array_dates)-1);
                $array_nigths_by_ownres[$own_reservation->getOwnResId()]=count($array_dates)-1;

                $insert=true;
                foreach($array_houses_ids as $item)
                {
                    if($own_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId() == $item)
                    {
                        $insert=false;
                    }
                }

                if($insert)
                {
                    array_push($array_houses_ids,$own_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId());
                    array_push($array_houses,$own_reservation->getOwnResGenResId()->getGenResOwnId());
                }
                if(isset($array_ownres_by_house[$own_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()]))
                    $temp_array=$array_ownres_by_house[$own_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()];
                else
                    $temp_array=array();

                array_push($temp_array,$own_reservation);
                $array_ownres_by_house[$own_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()]=$temp_array;
            }
            $em->flush();

            $response=$this->view_confirmation($booking->getBookingId());
            $user_locale = $user_tourist->getUserTouristLanguage()->getLangCode();
            $pdf_name='voucher'.$user_tourist->getUserTouristUser()->getUserId().'_'.$booking->getBookingId();
            $this->download_pdf($response, $pdf_name ,true);
            $attach="http://".$_SERVER['HTTP_HOST']."/web/vouchers/$pdf_name.pdf";

            // Enviando mail al cliente
            $service_email = $this->get('Email');

            $body=$this->render('frontEndBundle:mails:email_offer_available.html.twig',array(
                'booking'=>$booking->getBookingId(),
                'user'=>$user_tourist->getUserTouristUser(),
                'reservations'=>$own_reservations,
                'photos'=>$array_photos,
                'nights'=>$array_nigths,
                'user_locale' => $user_locale
            ));

            $locale = $this->get('translator');
            $subject = $locale->trans('PAYMENT_CONFIRMATION', array(), "messages", $user_locale);
            $service_email->send_email(
                $subject, 'reservation1@mycasaparticular.com', $subject.' - MyCasaParticular.com', $user_tourist->getUserTouristUser()->getUserEmail(), $body,$attach
            );


            // enviando mail a reservation team
            $payment_pending=0;
            $temp_tourist[0]=$user_tourist;
            foreach($array_ownres_by_house as $owns)
            {

                $body_res=$this->render('frontEndBundle:mails:rt_payment_confirmation.html.twig',array(
                    'user'=>$user_tourist->getUserTouristUser(),
                    'user_tourist'=>$temp_tourist,
                    'reservations'=>$owns,
                    'nights'=>$array_nigths_by_ownres,
                    'payment_pending'=>$payment_pending
                ));
                $service_email->send_email(
                    'Confirmación de pago', 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', 'reservation@mycasaparticular.com', $body_res
                );

            }

            // enviando mail al propietario
            foreach($array_ownres_by_house as $owns)
            {
                $body_prop=$this->render('frontEndBundle:mails:email_house_confirmation.html.twig',array(
                    'user'=>$user_tourist->getUserTouristUser(),
                    'user_tourist'=>$temp_tourist,
                    'reservations'=>$owns,
                    'nights'=>$array_nigths_by_ownres
                ));
                $prop_email=$owns[0]->getOwnResGenResId()->getGenResOwnId()->getOwnEmail1();
                if($prop_email)
                    $service_email->send_email(
                        'Confirmación de reserva', 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', $prop_email, $body_prop
                    );
            }
        }
        else
        {
            $custom_message = $this->getRequest()->get('message_to_client');
            if(isset($custom_message[0]))
                $custom_message[0]=strtoupper($custom_message[0]);
            $service_email->send_reservation($id_reservation,$custom_message);
        }

        $message='Reserva enviada satisfactoriamente';
        $this->get('session')->getFlashBag()->add('message_ok',$message);
        return $this->redirect($this->generateUrl('mycp_details_reservation',array('id_reservation'=>$id_reservation)));

    }

    public function edit_reservationAction($id_reservation,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $em = $this->getDoctrine()->getEntityManager();
        $reservation=$em->getRepository('mycpBundle:ownershipReservation')->get_reservation_by_id($id_reservation);

        $user_id=$reservation[0]['own_res_gen_res_id']['gen_res_user_id']['user_id'];
        $reservations_user=$em->getRepository('mycpBundle:ownershipReservation')->get_reservations_by_id_user($user_id);

        $data['total']=0;
        $data['post']=0;
        $errors=array();
        foreach($reservations_user as $reser)
        {

            $dif = $reser['own_res_reservation_from_date']->diff($reser['own_res_reservation_to_date']);
            $dif= $dif->format('%r%a');
            $data['total']+= $reser['own_res_night_price']*($dif-1);
        }
//        $currency_change=$reservation[0]['own_res_gen_res_id']['gen_res_user_id']['user_currency']['curr_cuc_change'];
        $currency_change=25;
        $data['total_cuc']=$data['total']/$currency_change;
        $service_cost=$em->getRepository('mycpBundle:config')->findAll();
        $data['service_cost_cuc']=$service_cost[0]->getConfServiceCost();
        $data['service_cost']=$data['service_cost_cuc']*$currency_change;

        $data['total_neto_cuc']=$data['total_cuc']+$data['service_cost_cuc'];
        $data['total_neto']=$data['total_neto_cuc']*$currency_change;

        $data['commission_percent_cuc']=$data['total_cuc']*$reservation[0]['own_res_commission_percent']/100;
        $data['commission_percent']=$data['commission_percent_cuc']*$currency_change;

        $data['avance_total_cuc']=$data['service_cost_cuc']+$data['commission_percent_cuc'];
        $data['avance_total']=$data['avance_total_cuc']*$currency_change;

        $data['pay_cuba_cuc']=$data['total_cuc']-$data['avance_total_cuc'];
        $data['pay_cuba']=$data['pay_cuba_cuc']*$currency_change;


        $post = $request->request->getIterator()->getArrayCopy();
        $post['selected_room']=$reservation[0]['own_res_selected_room'];
        if($request->getMethod()=='POST')
        {
            $data['ownership']= $request->get('ownership');
            $post['selected_room']=$request->get('selected_room');
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message="Este campo no puede estar vacío.";
            $array_keys=array_keys($post);
            $count=$errors_validation=$count_errors= 0;
            foreach ($post as $item) {
                $errors[$array_keys[$count]] = $errors_validation=$this->get('validator')->validateValue($item, $not_blank_validator);
                if($array_keys[$count]!='percent')
                    $count_errors+=count($errors_validation);
                $count++;
            }

            if ($count_errors == 0) {
                $em->getRepository('mycpBundle:ownershipReservation')->edit_reservation($reservation[0],$post);
                $message='Reserva actualizada satisfactoriamente.';
                $ownership= $em->getRepository('mycpBundle:ownership')->find($post['ownership']);

                $service_log= $this->get('log');
                $service_log->save_log('Edit entity for '.$ownership->getOwnMcpCode(),7);

                $this->get('session')->getFlashBag()->add('message_ok',$message);
                return $this->redirect($this->generateUrl('mycp_edit_reservation',array('id_reservation'=>$id_reservation)));
            }
        }
        else{
            $data['ownership']= $reservation[0]['own_res_own_id']['own_id'];
            $post['percent']=$reservation[0]['own_res_commission_percent'];
        }

        $reservation=$reservation[0];
        return $this->render('mycpBundle:reservation:reservationEdit.html.twig',array('errors'=>$errors,'data'=>$data,'reservation'=>$reservation,'id_reservation'=>$id_reservation,'post'=>$post));
    }

    public function get_ownershipsAction($data)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $ownerships=$em->getRepository('mycpBundle:ownership')->findAll();
        return $this->render('mycpBundle:utils:ownerships.html.twig',array('ownerships'=>$ownerships,'data'=>$data));

    }

    public function get_percent_listAction($post)
    {
        $selected='';
        if(isset($post['percent']))
            $selected=$post['percent'];
        return $this->render('mycpBundle:utils:percent.html.twig',array('selected'=>$selected));
    }

    public function get_numeric_listAction($post)
    {
        $selected='';
        if(isset($post['selected']))
            $selected=$post['selected'];
        return $this->render('mycpBundle:utils:range_max_4_no_0.html.twig',array('selected'=>$selected));
    }

    public function get_numeric_list_0Action($post)
    {
        $selected='';
        if(isset($post['selected']))
            $selected=$post['selected'];
        return $this->render('mycpBundle:utils:range_max_4.html.twig',array('selected'=>$selected));
    }

    public function get_reservation_statusAction($post)
    {
        $selected='';
        if(isset($post['selected']))
            $selected=$post['selected'];
        return $this->render('mycpBundle:utils:reservation_status.html.twig',array('selected'=>$selected));
    }

    public function get_rooms_by_ownershipAction($id_ownership,$selected_room)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $rooms=$em->getRepository('mycpBundle:room')->findBy(array('room_ownership'=>$id_ownership));
        return $this->render('mycpBundle:utils:rooms.html.twig',array('rooms'=>$rooms,'selected_room'=>$selected_room));
    }

    public function delete_reservationAction($id_reservation)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $reservation=$em->getRepository('mycpBundle:ownershipReservation')->find($id_reservation);
        $ownership= $em->getRepository('mycpBundle:ownership')->find($reservation->getOwnResOwnId());
        $em->remove($reservation->getOwnResGenResId());
        $em->remove($reservation);
        $em->flush();
        $message='Reserva eliminada satisfactoriamente.';

        $service_log= $this->get('log');
        $service_log->save_log('Delete entity for '.$ownership->getOwnMcpCode(),7);

        $this->get('session')->getFlashBag()->add('message_ok',$message);
        return $this->redirect($this->generateUrl('mycp_list_reservations'));
    }

    function get_sort_byAction($sort_by)
    {
        $selected='';
        if(isset($sort_by))
            $selected=$sort_by;
        return $this->render('mycpBundle:utils:reservation_sort_by.html.twig',array('selected'=>$selected));
    }

    function view_confirmation($id_booking)
    {
        $service_time = $this->get('Time');
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $own_res = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_reservation_booking' => $id_booking));

        $booking = $em->getRepository('mycpBundle:booking')->findBy(array('booking_id'=>$id_booking));

        if(!$booking)
        {
            throw $this->createNotFoundException();
        }
        $booking=$booking[0];
        $nights = array();
        $rooms = array();
        $commissions = array();
        $total_price = 0;
        $total_percent_price = 0;
        foreach ($own_res as $own) {
            $array_dates = $service_time->dates_between($own->getOwnResReservationFromDate()->getTimestamp(), $own->getOwnResReservationToDate()->getTimestamp());
            array_push($nights, count($array_dates) - 1);
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($own->getOwnResSelectedRoomId()));
            $total_price += $own->getOwnResNightPrice() * (count($array_dates) - 1);
            $commission = $own->getOwnResGenResId()->GetGenResOwnId()->getOwnCommissionPercent();
            $total_percent_price += $own->getOwnResNightPrice() * (count($array_dates) - 1) * $commission / 100;
            $insert = 1;
            foreach ($commissions as $com) {
                if ($com == $commission) {
                    $insert = 0;
                    break;
                }
            }
            if ($insert == 1) {
                array_push($commissions, $commission);
            }
        }
            return $this->renderView('frontEndBundle:reservation:boucherReservation.html.twig', array(
                'own_res' => $own_res,
                'user' => $user,
                'booking' => $booking,
                'nights' => $nights,
                'rooms' => $rooms,
                'total_price' => $total_price,
                'total_percent_price' => $total_percent_price,
                'commissions' => $commissions
            ));

    }

    function download_pdf($html, $name, $save_to_disk = false, $id_booking = null) {

        require_once($this->get('kernel')->getRootDir().'/config/dompdf_config.inc.php');
        $dompdf = new \DOMPDF();
        $dompdf->load_html($html);
        $dompdf->set_paper("a4");
        $dompdf->render();

        if($save_to_disk==true)
        {
            $content_out=$dompdf->output();
            $fpdf = fopen("vouchers/$name.pdf", 'w');
            fwrite($fpdf, $content_out);
            fclose($fpdf);
        }
        else
            $dompdf->stream($name . ".pdf", array("Attachment" => false));
    }
}
