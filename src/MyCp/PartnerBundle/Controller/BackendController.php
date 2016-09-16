<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\mycpBundle\Helpers\Dates;
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

        $em->getRepository("PartnerBundle:paReservation")->newReservation($travelAgency, $clientName, $adults, $children, $dateFrom, $dateTo, $accommodation, $user, $this->container);

        $list = $em->getRepository('PartnerBundle:paReservation')->getOpenReservationsList($travelAgency);

        $response = $this->renderView('PartnerBundle:Modal:open-reservations-list.html.twig', array(
            'list' => $list
        ));

        return new JsonResponse([
            'success' => true,
            'html' => $response,

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

        ]);

    }
}
