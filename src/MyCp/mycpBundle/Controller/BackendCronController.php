<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class BackendCronController extends Controller {

    public function reminder_availableAction()
    {
        // debe ser ejecutado cada una hora
        $em = $this->getDoctrine()->getManager();
        $gen_reservations =  $em->getRepository('mycpBundle:generalReservation')->get_reminder_available();
        $array_photos=array();
        $array_nigths=array();
        $service_time=$this->get('time');
        $service_email = $this->get('Email');
        if($gen_reservations)
        foreach($gen_reservations as $gen_reservation)
        {
            $reservations=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$gen_reservation->getGenResId()));

            foreach($reservations as $res)
            {
                $photos=$em->getRepository('mycpBundle:ownership')->getPhotos($res->getOwnResGenResId()->getGenResOwnId()->getOwnId());
                array_push($array_photos,$photos);
                $array_dates= $service_time->dates_between($res->getOwnResReservationFromDate()->getTimestamp(),$res->getOwnResReservationToDate()->getTimestamp());
                array_push($array_nigths,count($array_dates));
            }
            // Enviando mail al cliente
            $user_tourist=$em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user'=>$gen_reservation->getGenResUserId()->getUserId()));
            $locale = $this->get('translator');
            $this->get('translator')->setLocale(strtolower($user_tourist->getUserTouristLanguage()->getLangCode()));
            $body = $this->render('frontEndBundle:mails:reminder_available.html.twig', array(
                'user'=>$gen_reservation->getGenResUserId(),
                'reservations'=>$reservations,
                'photos'=>$array_photos,
                'nights'=>$array_nigths
            ));

            $subject = $locale->trans('REMINDER');
            $service_email->send_email(
                $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com',
                $gen_reservation->getGenResUserId()->getUserEmail(),
                $body
            );
        }

        return new Response('Operation terminada!!!');

    }

    public function time_overAction()
    {
        // debe ser ejecutado cada una hora
        $em = $this->getDoctrine()->getManager();
        $gen_reservations =  $em->getRepository('mycpBundle:generalReservation')->get_time_over_reservations();
        if($gen_reservations)
        foreach($gen_reservations as $gen_reservation)
        {
            $gen_reservation->setGenResStatus(3);
            $em->persist($gen_reservation);
            $reservations=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$gen_reservation->getGenResId()));
            foreach($reservations as $res)
            {
                $res->setOwnResStatus(3);
                $em->persist($res);
            }
        }
        $em->flush();
        return new Response('Operation terminada!!!');
    }

}
