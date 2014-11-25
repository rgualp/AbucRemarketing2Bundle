<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\generalReservation;

class BackendTestEmailTemplateController extends Controller {

    public function homeAction()
    {
         return $this->render('mycpBundle:test:home.html.twig');
    }

    public function lastChanceToBookAction($langCode)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $service_time = $this->get('time');

        $generalReservation = $em
                ->getRepository('mycpBundle:generalReservation')
                ->findOneBy(array('gen_res_status'=> generalReservation::STATUS_AVAILABLE));

        $user = $generalReservation->getGenResUserId();
        $userTourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array("user_tourist_user" => $user->getUserId()));

        $ownershipReservations = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        $arrayPhotos = array();
        $arrayNights = array();

        $initialPayment = 0;

        foreach($ownershipReservations as $ownershipReservation)
        {
            $photos = $em
                ->getRepository('mycpBundle:ownership')
                ->getPhotos(
                    // TODO: This line is very strange. Why take the ownId of the genRes of the ownRes?!
                    $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()
                );

            if(!empty($photos)) {
                array_push($arrayPhotos, $photos);
            }

            $array_dates = $service_time
                ->datesBetween(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(),
                    $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
                );

            array_push($arrayNights, count($array_dates) - 1);

            $comission = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent()/100;
            //Initial down payment
            if($ownershipReservation->getOwnResNightPrice() > 0)
                $initialPayment += $ownershipReservation->getOwnResNightPrice() * (count($array_dates) - 1) * $comission;
            else
                $initialPayment += $ownershipReservation->getOwnResTotalInSite() * $comission;
        }

        return $this->render('FrontEndBundle:mails:last_reminder_available.html.twig', array(
                'user' => $user,
                'reservations' => $ownershipReservations,
                'photos' => $arrayPhotos,
                'nights' => $arrayNights,
                'user_locale' => $langCode,
                'initial_payment' => $initialPayment,
                'generalReservationId' => $generalReservation->getGenResId(),
                'user_currency' => ($userTourist != null) ? $userTourist->getUserTouristCurrency() : null
            ));
    }

    public function accommodationStillAvailableAction($langCode)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $service_time = $this->get('time');

        $generalReservation = $em
                ->getRepository('mycpBundle:generalReservation')
                ->findOneBy(array('gen_res_status'=> generalReservation::STATUS_AVAILABLE));

        $user = $generalReservation->getGenResUserId();
        $userTourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array("user_tourist_user" => $user->getUserId()));
        $ownershipReservations = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        $arrayPhotos = array();
        $arrayNights = array();

        $initialPayment = 0;

        foreach($ownershipReservations as $ownershipReservation)
        {
            $photos = $em
                ->getRepository('mycpBundle:ownership')
                ->getPhotos(
                    // TODO: This line is very strange. Why take the ownId of the genRes of the ownRes?!
                    $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()
                );

            if(!empty($photos)) {
                array_push($arrayPhotos, $photos);
            }

            $array_dates = $service_time
                ->datesBetween(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(),
                    $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
                );

            array_push($arrayNights, count($array_dates) - 1);

            $comission = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent()/100;
            //Initial down payment
            if($ownershipReservation->getOwnResNightPrice() > 0)
                $initialPayment += $ownershipReservation->getOwnResNightPrice() * (count($array_dates) - 1) * $comission;
            else
                $initialPayment += $ownershipReservation->getOwnResTotalInSite() * $comission;
        }

        return $this->render('FrontEndBundle:mails:reminder_available.html.twig', array(
                'user' => $user,
                'reservations' => $ownershipReservations,
                'photos' => $arrayPhotos,
                'nights' => $arrayNights,
                'user_locale' => $langCode,
                'initial_payment' => $initialPayment,
                'user_currency' => ($userTourist != null) ? $userTourist->getUserTouristCurrency() : null
            ));
    }

    public function notAvailableAction($langCode)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $service_time = $this->get('time');

        $generalReservation = $em
                ->getRepository('mycpBundle:generalReservation')
                ->findOneBy(array('gen_res_status'=> generalReservation::STATUS_AVAILABLE));

        $user = $generalReservation->getGenResUserId();
        $userTourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array("user_tourist_user" => $user->getUserId()));

        $ownershipReservations = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        $arrayPhotos = array();
        $arrayNights = array();

        $initialPayment = 0;

        foreach($ownershipReservations as $ownershipReservation)
        {
            $photos = $em
                ->getRepository('mycpBundle:ownership')
                ->getPhotos(
                    // TODO: This line is very strange. Why take the ownId of the genRes of the ownRes?!
                    $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()
                );

            if(!empty($photos)) {
                array_push($arrayPhotos, $photos);
            }

            $array_dates = $service_time
                ->datesBetween(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(),
                    $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
                );

            array_push($arrayNights, count($array_dates) - 1);

            $comission = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent()/100;
            //Initial down payment
            if($ownershipReservation->getOwnResNightPrice() > 0)
                $initialPayment += $ownershipReservation->getOwnResNightPrice() * (count($array_dates) - 1) * $comission;
            else
                $initialPayment += $ownershipReservation->getOwnResTotalInSite() * $comission;
        }

        return $this->render('FrontEndBundle:mails:expired_offer_reminder.html.twig', array(
                'user' => $user,
                'reservations' => $ownershipReservations,
                'photos' => $arrayPhotos,
                'nights' => $arrayNights,
                'user_locale' => $langCode,
                'initial_payment' => $initialPayment,
                'generalReservationId' => $generalReservation->getGenResId(),
                'user_currency' => ($userTourist != null) ? $userTourist->getUserTouristCurrency() : null
            ));
    }

    public function activateAccountAction($langCode)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_enabled' => true));
        $activationUrl = $this->getActivationUrl($user, $langCode);
        $userName = $user->getUserCompleteName();

         return $this->render('FrontEndBundle:mails:enableAccount.html.twig', array(
                'enableUrl' => $activationUrl,
                'user_name' => $userName,
                'user_locale' => $langCode
            ));
    }

    public function activateAccountReminderAction($langCode)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_enabled' => true));
        $activationUrl = $this->getActivationUrl($user, $langCode);
        $userName = $user->getUserCompleteName();

         return $this->render('FrontEndBundle:mails:enableAccountReminder.html.twig', array(
                'enableUrl' => $activationUrl,
                'user_name' => $userName,
                'user_locale' => $langCode
            ));
    }

    public function activateAccountLastReminderAction($langCode)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_enabled' => true));
        $activationUrl = $this->getActivationUrl($user, $langCode);
        $userName = $user->getUserCompleteName();

         return $this->render('FrontEndBundle:mails:enableAccountLateReminder.html.twig', array(
                'enableUrl' => $activationUrl,
                'user_name' => $userName,
                'user_locale' => $langCode
            ));
    }

    public function cartFullReminderAction($langCode)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array());
        $userName = $userTourist->getUserTouristUser()->getUserCompleteName();
        $cartItems = $em->getRepository('mycpBundle:cart')->testValues($userTourist->getUserTouristUser());
        $service_time = $this->get('Time');

        $accommodations = array();
        $cartAccommodations = array();
        $cartPrices = array();

        $current_own_id = 0;

        foreach($cartItems as $item)
        {
            if($item->getCartRoom()->getRoomOwnership()->getOwnId() != $current_own_id)
            {
                $current_own_id = $item->getCartRoom()->getRoomOwnership()->getOwnId();
                array_push($accommodations, $item->getCartRoom()->getRoomOwnership());

                $cartAccommodations[$current_own_id] = array();
                $cartPrices[$current_own_id] = array();
            }

            array_push($cartAccommodations[$current_own_id], $item);
            array_push($cartPrices[$current_own_id], $item->calculatePrice($em,$service_time,$this->container->getParameter('configuration.triple.room.charge'), $this->container->getParameter('configuration.service.fee')));
        }

        $photos = $em->getRepository("mycpBundle:ownership")->get_photos_array($accommodations);

         return $this->render('FrontEndBundle:mails:cartFull.html.twig', array(
                'user_name' => $userName,
                'user_locale' => $langCode,
                'owns' => $accommodations,
                'cartItems' => $cartAccommodations,
                'photos' => $photos,
                'prices' => $cartPrices,
                'user_currency' => $userTourist->getUserTouristCurrency()
            ));
    }
    
    public function feedbackReminderAction($langCode)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array());
        $userName = $userTourist->getUserTouristUser()->getUserCompleteName();
        $cartItems = $em->getRepository('mycpBundle:cart')->testValues($userTourist->getUserTouristUser());
        $service_time = $this->get('Time');

        $accommodations = array();
        $cartAccommodations = array();
        $cartPrices = array();

        $current_own_id = 0;

        foreach($cartItems as $item)
        {
            if($item->getCartRoom()->getRoomOwnership()->getOwnId() != $current_own_id)
            {
                $current_own_id = $item->getCartRoom()->getRoomOwnership()->getOwnId();
                array_push($accommodations, $item->getCartRoom()->getRoomOwnership());

                $cartAccommodations[$current_own_id] = array();
                $cartPrices[$current_own_id] = array();
            }

            array_push($cartAccommodations[$current_own_id], $item);
            array_push($cartPrices[$current_own_id], $item->calculatePrice($em,$service_time,$this->container->getParameter('configuration.triple.room.charge'), $this->container->getParameter('configuration.service.fee')));
        }

        $photos = $em->getRepository("mycpBundle:ownership")->get_photos_array($accommodations);

         return $this->render('FrontEndBundle:mails:feedback.html.twig', array(
                'user_name' => $userName,
                'user_locale' => $langCode,
                'owns' => $accommodations,
                'cartItems' => $cartAccommodations,
                'photos' => $photos,
                'prices' => $cartPrices,
                'user_currency' => $userTourist->getUserTouristCurrency()
            ));
    }

    private function getActivationUrl($user, $userLocale)
    {
        $encodedString =$this->get('Secure')->getEncodedUserString($user);
        $enableUrl = $this->get('router')->generate('frontend_enable_user', array(
            'string' => $encodedString,
            'locale' => $userLocale,
            '_locale' => $userLocale), true);
        return $enableUrl;
    }
}
