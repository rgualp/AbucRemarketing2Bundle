<?php

namespace MyCp\MobileFrontendBundle\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MyCp\mycpBundle\Entity\ownershipReservation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MobileController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('MyCpMobileFrontendBundle:Default:index.html.twig', array('name' => $name));
    }

    public function topNavAction($route, $routeParams = null)
    {
        $routeParams = empty($routeParams) ? array() : $routeParams;

        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);

        $countItems = $em->getRepository('mycpBundle:favorite')->getTotal($user_ids['user_id'],$user_ids['session_id']);
        $response = $this->render('MyCpMobileFrontendBundle:menus:topnav.html.twig', array(
            'route' => $route,
            'routeParams' => $routeParams,
            'count_fav'=>$countItems
        ));

        return $response;
    }

    public function langCurrAction($route, $routeParams = null)
    {
        $routeParams = empty($routeParams) ? array() : $routeParams;

        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);

        $countItems = $em->getRepository('mycpBundle:favorite')->getTotal($user_ids['user_id'],$user_ids['session_id']);
        $response = $this->render('@MyCpMobileFrontend/menus/language_currency.html.twig', array(
            'route' => $route,
            'routeParams' => $routeParams,
            'count_fav'=>$countItems
        ));

        return $response;
    }

    public function homeCarrouselAction() {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);

        $popular_destinations_list = $em->getRepository('mycpBundle:destination')->getPopularDestinations(12, $user_ids['user_id'], $user_ids['session_id']);

        return $this->render('@MyCpMobileFrontend/destination/homeCarrousel.html.twig', array(
            'popular_places' => $popular_destinations_list
        ));
    }
    public function countCestaItemsAction() {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $date = \date('Y-m-j');

        $string_sql = "AND gre.gen_res_from_date >= '$date'";
        $status_string = 'ownre.own_res_status =' . ownershipReservation::STATUS_AVAILABLE;
        $list = ($user!='')?$em->getRepository('mycpBundle:ownershipReservation')->findByUserAndStatus($user->getUserId(), $status_string, $string_sql):array();


        return $this->render('MyCpMobileFrontendBundle:utils:alert-cart.html.twig', array(
            'count' => count($list)

        ));
    }
    public function countCestatItemsAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        // disponibles Mayores que (hoy - 30) días
        $date = \date('Y-m-j');
        //$new_date = strtotime('-60 hours', strtotime($date));
        //$new_date = \date('Y-m-j', $new_date);
        //$string_sql = "AND gre.gen_res_status_date > '$new_date'";
        $string_sql = "AND gre.gen_res_from_date >= '$date'";
        $status_string = 'ownre.own_res_status =' . ownershipReservation::STATUS_AVAILABLE;
        $list = ($user!='')?$em->getRepository('mycpBundle:ownershipReservation')->findByUserAndStatus($user->getUserId(), $status_string, $string_sql):array();

        $search = $request->get('search') ? $request->get('search') : false;

        return $this->render('FrontEndBundle:cart:cestaCountItems.html.twig', array(
            'count' => count($list),
            'search' => $search
        ));
    }
    public function downloadVoucherAction(Request $request) {
                $em = $this->getDoctrine()->getManager();
                $idgenres=$request->get('id');

                $bookings_ids = $em->getRepository('mycpBundle:generalReservation')->getBookings($idgenres);
                $booking = $em->getRepository('mycpBundle:booking')->find($bookings_ids[0]);
                $pdfservice = $this->get("front_end.services.booking");
                $name = 'voucher' . $booking->getBookingUserId() . '_' . $booking->getBookingId() . '.pdf';
                $pathToFile = $pdfservice->getVoucherFilePathByBookingId($booking->getBookingId());

                if (!file_exists(realpath($pathToFile))) {
                     $pathToFile = $pdfservice->createBookingVoucherIfNotExisting($booking->getBookingId());
                }
                $response = new BinaryFileResponse($pathToFile);
                $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $name);

                return $response;
        }
}
