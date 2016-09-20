<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\mycpBundle\Helpers\Dates;
use MyCp\PartnerBundle\Entity\paReservationDetail;
use MyCp\PartnerBundle\Form\paReservationType;
use MyCp\PartnerBundle\Form\paTravelAgencyType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MyCp\PartnerBundle\Form\FilterOwnershipType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\PartnerBundle\Entity\paTravelAgency;

class BackendController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('mycpBundle:ownership')->getSearchNumbers();

        $categories_own_list = $results["categories"];
        $types_own_list = $results["types"];
        $prices_own_list = $results["prices"];
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();


        $form = $this->createForm(new paReservationType($this->get('translator')));
        $formFilterOwnerShip = $this->createForm(new FilterOwnershipType($this->get('translator'), array()));
        return $this->render('PartnerBundle:Backend:index.html.twig', array(
            "locale" => "es",
            "owns_categories" => null,
            "autocomplete_text_list" => null,
            "owns_prices" => $prices_own_list,
            "formFilterOwnerShip"=>$formFilterOwnerShip->createView(),
            'form'=>$form->createView()
        ));
    }

    /**
     * @param Request $request
     */
    public function searchAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $filters= $request->get('requests_ownership_filter');
        $start=$request->request->get('start');
        $limit=$request->request->get('limit');
        $list =$em->getRepository('mycpBundle:ownership')->searchOwnership($this,$filters,$start,$limit);
        $response = $this->renderView('PartnerBundle:Search:result.html.twig', array(
            'list' => $list
        ));
        $result = array();
        if (count($list)) {
            foreach ($list as $own) {
                $prize = ($own['minimum_price']) * ($session->get('curr_rate') == null ? 1 : $session->get('curr_rate'));
                $result[] = array('latitude' => $own['latitude'],
                    'longitude' => $own['longitude'],
                    'title' => $own['own_name'],
                    'content' => $this->get('translator')->trans('FROM_PRICES') . ($session->get("curr_symbol") != null ? " " . $session->get('curr_symbol') . " " : " $ ") . $prize . " " . strtolower($this->get('translator')->trans("BYNIGHTS_PRICES")),
                    'image' => $this->container->get('templating.helper.assets')->getUrl('uploads/ownershipImages/thumbnails/' . $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($own['own_id'])),
                    'id' => $own['own_id'],
                    'url'=>$this->generateUrl('frontend_details_ownership', array('own_name' => $own['own_name'])));
            }
        }
        return new JsonResponse(array('response_twig'=>$response,'response_json'=>$result));
    }

    public function openReservationsListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $list = $em->getRepository('PartnerBundle:paReservation')->getOpenReservationsList($tourOperator->getTravelAgency());

        $response = $this->renderView('PartnerBundle:Modal:open-reservations-list.html.twig', array(
            'list' => $list
        ));

        return new Response($response, 200);
    }


    /**
     * @return JsonResponse
     */
    public function newReservationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //Adding new reservation
        $clientName = $request->get("clientName");
        $dateFrom = $request->get("dateFrom");
        $dateTo = $request->get("dateTo");
        $adults = $request->get("adults");
        $children = $request->get("children");
        $accommodationId = $request->get("accommodationId");

        $dateFrom = Dates::createDateFromString($dateFrom,"/", 1);
        $dateTo = Dates::createDateFromString($dateTo,"/", 1);

        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $accommodation = $em->getRepository("mycpBundle:ownership")->find($accommodationId);

        $returnedObject = $em->getRepository("PartnerBundle:paReservation")->newReservation($travelAgency, $clientName, $adults, $children, $dateFrom, $dateTo, $accommodation, $user, $this->container);
        $locale = $this->get('translator');
        $message = $locale->trans($returnedObject["message"]);

        $list = $em->getRepository('PartnerBundle:paReservation')->getOpenReservationsList($travelAgency);

        $response = $this->renderView('PartnerBundle:Modal:open-reservations-list.html.twig', array(
            'list' => $list
        ));

        return new JsonResponse([
            'success' => true,
            'html' => $response,
            'message' => $message

        ]);
    }

    /**
     * @return JsonResponse
     */
    public function closeReservationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //Closing reservation
        $id = $request->get("id");

        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $reservation = $em->getRepository("PartnerBundle:paReservation")->find($id);

        //Pasar el paGeneralReservation a generalReservation
        $reservationDetails = $reservation->getDetails();

        foreach($reservationDetails as $detail)
        {
            $paGeneralReservation = $detail->getOpenReservationDetail(); // a eliminar
            $paOwnershipReservations = $paGeneralReservation->getPaOwnershipReservations(); //a eliminar una a una

            $generalReservation = $paGeneralReservation->createReservation();

            //Pasar los paOwnershipReservation a ownershipReservation
            foreach($paOwnershipReservations as $paORes){
                $ownershipReservation = $paORes->createReservation();
                $ownershipReservation->setOwnResGenResId($generalReservation);

                $em->remove($paORes); //Eliminar paOwnershipReservation
                $em->persist($ownershipReservation);
            }

            //Eliminar el OpenReservationDetail y actualizar el ReservationDetail
            $detail->setOpenReservationDetail(null);
            $detail->setReservationDetail($generalReservation);
            $em->persist($detail);

            $em->persist($generalReservation);
            $em->remove($paGeneralReservation); //Eliminar paGeneralReservation
            $em->flush();
        }

        $reservation->setClosed(true);
        $em->persist($reservation);
        $em->flush();

        $list = $em->getRepository('PartnerBundle:paReservation')->getOpenReservationsList($travelAgency);

        $response = $this->renderView('PartnerBundle:Modal:open-reservations-list.html.twig', array(
            'list' => $list
        ));

        return new JsonResponse([
            'success' => true,
            'html' => $response,
            'message' => ""

        ]);

    }

    /**
     * @return JsonResponse
     */
    public function addReservationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //Adding to opened reservation
        $id = $request->get("id");
        $dateFrom = $request->get("dateFrom");
        $dateTo = $request->get("dateTo");
        $accommodationId = $request->get("accommodationId");

        $dateFrom = Dates::createDateFromString($dateFrom,"/", 1);
        $dateTo = Dates::createDateFromString($dateTo,"/", 1);

        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $accommodation = $em->getRepository("mycpBundle:ownership")->find($accommodationId);

        $reservation = $em->getRepository("PartnerBundle:paReservation")->findOneBy(array("id" => $id, "closed" => false));
        $message = "";

        if(isset($reservation))
        {
            $adults = $reservation->getAdults();
            $children = $reservation->getChildren();
            //Actualizar total de ubicados
            $reservation->setAdultsWithAccommodation($reservation->getAdultsWithAccommodation() + $adults);
            $reservation->setChildrenWithAccommodation($reservation->getChildrenWithAccommodation() + $children);
            $em->persist($reservation);

            //Agregar un generalReservation por casa
            $returnedObject = $em->getRepository("PartnerBundle:paGeneralReservation")->createReservationForPartner($user, $accommodation, $dateFrom, $dateTo, $adults, $children, $this->container);
            $locale = $this->get('translator');
            $message = $locale->trans($returnedObject["message"]);

            if($returnedObject["successful"])
            {
                $detail = new paReservationDetail();
                $detail->setReservation($reservation)
                    ->setOpenReservationDetail($returnedObject["reservation"]);

                $em->persist($detail);
                $em->flush();
            }
        }

        $list = $em->getRepository('PartnerBundle:paReservation')->getOpenReservationsList($travelAgency);

        $response = $this->renderView('PartnerBundle:Modal:open-reservations-list.html.twig', array(
            'list' => $list
        ));

        return new JsonResponse([
            'success' => true,
            'html' => $response,
            'message' => $message

        ]);

    }

    public function detailedOpenReservationsListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $reservationId = $request->get("reservationId");
        $reservationNumber = $request->get("reservationNumber");
        $clientName = $request->get("clientName");
        $creationDate = $request->get("creationDate");

        $reservation = $em->getRepository('PartnerBundle:paReservation')->find($reservationId);

        $response = $this->renderView('PartnerBundle:Modal:detailed-open-reservations-list.html.twig', array(
            'reservation' => $reservation,
            'number' => $reservationNumber,
            'creationDate' => $creationDate,
            'clientName' => $clientName
        ));

        return new JsonResponse([
            'success' => true,
            'html' => $response,
            'message' => ""

        ]);
    }
}
