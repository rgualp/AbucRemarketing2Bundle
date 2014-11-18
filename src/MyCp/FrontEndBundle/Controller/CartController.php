<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\season;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\cart;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use MyCp\FrontEndBundle\Helpers\ReservationHelper;

class CartController extends Controller {

    public function countCartItemsAction() {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);
        $countItems = $em->getRepository('mycpBundle:cart')->countItems($user_ids);

        return $this->render('FrontEndBundle:cart:cartCountItems.html.twig', array(
                    'count' => $countItems
        ));
    }

    public function emptyCartAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);
        $em->getRepository('mycpBundle:cart')->emptyCart($user_ids);
        $trans = $this->get('translator');
        $message = $trans->trans('CART_EMPTY_SUCCESSFUL');
        $this->get('session')->getFlashBag()->add('message_global_success', $message);
        return $this->redirect($this->generateUrl('frontend_view_cart'));
    }

    public function addToCartAction($id_ownership, Request $request) {
        $em = $this->getDoctrine()->getManager();
        if (!$request->get('data_reservation'))
            throw $this->createNotFoundException();
        $data = $request->get('data_reservation');
        //var_dump($data); exit();
        $data = explode('/', $data);

        $from_date = $data[0];
        $to_date = $data[1];
        $ids_rooms = $data[2];
        $count_guests = $data[3];
        $count_kids = $data[4];
        $array_ids_rooms = explode('&', $ids_rooms);
        array_shift($array_ids_rooms);
        $array_count_guests = explode('&', $count_guests);
        array_shift($array_count_guests);
        $array_count_kids = explode('&', $count_kids);
        array_shift($array_count_kids);

        $reservation_date_from = $from_date;
        $reservation_date_from = explode('&', $reservation_date_from);

        $start_timestamp = mktime(0, 0, 0, $reservation_date_from[1], $reservation_date_from[0], $reservation_date_from[2]);

        $reservation_date_to = $to_date;
        $reservation_date_to = explode('&', $reservation_date_to);
        $end_timestamp = mktime(0, 0, 0, $reservation_date_to[1], $reservation_date_to[0], $reservation_date_to[2]);

        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);
        $cartItems = $em->getRepository('mycpBundle:cart')->getCartItems($user_ids);

        for ($a = 0; $a < count($array_ids_rooms); $a++) {
            $insert = 1;
            foreach ($cartItems as $item) {
                if (isset($array_count_guests[$a]) && isset($array_count_kids[$a]) &&
                        $item->getCartDateFrom()->getTimestamp() == $start_timestamp &&
                        $item->getCartDateTo()->getTimestamp() == $end_timestamp &&
                        $item->getCartRoom() == $array_ids_rooms[$a] &&
                        $item->getCartCountAdults() == $array_count_guests[$a] &&
                        $item->getCartCountChildren() == $array_count_kids[$a]
                ) {
                    $insert = 0;
                }
            }
            if ($insert == 1) {
                $room = $em->getRepository('mycpBundle:room')->find($array_ids_rooms[$a]);
                $cart = new cart();
                $fromDate = new \DateTime();
                $fromDate->setTimestamp($start_timestamp);
                $cart->setCartDateFrom($fromDate);

                $toDate = new \DateTime();
                $toDate->setTimestamp($end_timestamp);
                $cart->setCartDateTo($toDate);
                $cart->setCartRoom($room);

                if (isset($array_count_guests[$a]))
                    $cart->setCartCountAdults($array_count_guests[$a]);
                else
                    $cart->setCartCountAdults(1);

                if (isset($array_count_kids[$a]))
                    $cart->setCartCountChildren($array_count_kids[$a]);
                else
                    $cart->setCartCountChildren(0);

                $cart->setCartCreatedDate(new \DateTime());
                if ($user_ids["user_id"] != null) {
                    $user = $em->getRepository("mycpBundle:user")->find($user_ids["user_id"]);
                    $cart->setCartUser($user);
                } else if ($user_ids["session_id"] != null)
                    $cart->setCartSessionId($user_ids["session_id"]);

                $em->persist($cart);
                $em->flush();
            }
        }
        
        return $this->redirect($this->generateUrl('frontend_view_cart'));
    }

    public function removeFromCartAction($data, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $array_data = explode('-', $data);
        $cartItem = $em->getRepository("mycpBundle:cart")->find($array_data[0]);


        if ($cartItem->getCartDateFrom()->getTimestamp() == $array_data[1]) {
            $date = new \DateTime();
            $date->setTimestamp(strtotime("+1 day", $cartItem->getCartDateFrom()->getTimestamp()));
            $cartItem->setCartDateFrom($date);
            //$service['from_date'] += 86400;
        } else if ($cartItem->getCartDateTo()->getTimestamp() == $array_data[1]) {
            $dateTo = new \DateTime();
            $dateTo->setTimestamp(strtotime("-1 day", $cartItem->getCartDateTo()->getTimestamp()));
            $cartItem->setCartDateTo($dateTo);
            //$service['to_date'] -= 86400;
        } else if ($array_data[1] < $cartItem->getCartDateTo()->getTimestamp() && $array_data[1] > $cartItem->getCartDateFrom()->getTimestamp()) {
            $cartItemNext = $cartItem->getClone();
            $date = new \DateTime();
            $date->setTimestamp(strtotime("-1 day", $array_data[1]));
            $cartItem->setCartDateTo($date);
            //$service['to_date'] = $array_data[1] - 86400;

            $date = new \DateTime();
            $date->setTimestamp($array_data[1]);
            $cartItemNext->setCartDateFrom($date);
            //$service_next['from_date'] = $array_data[1] + 86400;
            $em->persist($cartItemNext);
        }

        $em->persist($cartItem);
        /* if ($cartItem->getCartDateTo()->getTimestamp() <= $cartItem->getCartDateFrom()->getTimestamp()) {
          //eliminar el cartItem
          $em->remove($cartItem);
          } */
        //var_dump($services);
        //$request->getSession()->set('services_pre_reservation', $services);
        $em->flush();
        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);
        $cartItems = $em->getRepository('mycpBundle:cart')->getCartItems($user_ids);

        foreach ($cartItems as $item) {
            if ($item->getCartDateTo()->getTimestamp() <= $item->getCartDateFrom()->getTimestamp()) {
                //eliminar el cartItem
                $em->remove($cartItem);
            }
        }
        $em->flush();

        if (count($cartItems) < 1) {
            return new Response('0');
        }
        return $this->getCartBodyAction($request);
    }

    public function viewCartAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        /* $last_own = $request->getSession()->get('services_pre_reservation_last_own');
          if ($last_own)
          $ownership = $em->getRepository('mycpBundle:ownership')->find($last_own);
          else
          $ownership = 0; */

        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);
        $countItems = $em->getRepository('mycpBundle:cart')->countItems($user_ids);

        return $this->render('FrontEndBundle:cart:cart.html.twig', array(
                    'countItems' => $countItems,
        ));
    }

    public function getCartBodyAction() {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);
        $cartItems = $em->getRepository('mycpBundle:cart')->getCartItems($user_ids);

        $min_date = 0;
        $max_date = 0;

        foreach ($cartItems as $item) {
            if ($min_date == 0)
                $min_date = $item->getCartDateFrom()->getTimestamp();
            else if ($item->getCartDateFrom()->getTimestamp() < $min_date)
                $min_date = $item->getCartDateFrom()->getTimestamp();

            if ($max_date == 0)
                $max_date = $item->getCartDateTo()->getTimestamp();
            else if ($item->getCartDateTo()->getTimestamp() > $max_date)
                $max_date = $item->getCartDateTo()->getTimestamp();
        }

        $service_time = $this->get('Time');
        $array_dates = $service_time->datesBetween($min_date, $max_date);
        $array_dates_string_day = array();
        $array_dates_string = array();
        $array_season = array();
        $array_clear_date = array();

        if ($array_dates) {
            $em = $this->getDoctrine()->getManager();
            foreach ($array_dates as $date) {
                array_push($array_dates_string, \date('/m/Y', $date));
                array_push($array_dates_string_day, \date('d', $date));

                $insert = 1;
                $array_season_temp = array();
                foreach ($cartItems as $item) {
                    $destination = $item->getCartRoom()->getRoomOwnership()->getOwnDestination();
                    $destination_id = isset($destination) ? $item->getCartRoom()->getRoomOwnership()->getOwnDestination()->getDesId() : null;
                    $seasons = $em->getRepository("mycpBundle:season")->getSeasons($min_date, $max_date, $destination_id);
                    $seasonTypes = $service_time->seasonByDate($seasons, $date);
                    array_push($array_season_temp, $seasonTypes);

                    if ($date >= $item->getCartDateFrom()->getTimestamp() && $date <= $item->getCartDateTo()->getTimestamp()) {
                        $insert = 0;
                    }
                }
                if ($insert == 1) {
                    $array_clear_date[$date] = 1;
                }

                $array_season[$date] = $array_season_temp;
            }
        }

        return $this->render('FrontEndBundle:cart:bodyCart.html.twig', array(
                    'dates_string' => $array_dates_string,
                    'dates_string_day' => $array_dates_string_day,
                    'dates_timestamp' => $array_dates,
                    'cartItems' => $cartItems,
                    'array_season' => $array_season,
                    'array_clear_date' => $array_clear_date
        ));
    }
}
