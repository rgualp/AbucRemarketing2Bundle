<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\mycpBundle\Helpers\Dates;
use MyCp\PartnerBundle\Entity\paReservationDetail;
use MyCp\PartnerBundle\Form\paReservationExtendedType;
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

//        $bookingService = $this->get("front_end.services.booking");
//        return $bookingService->getPrintableBookingConfirmationResponsePartner(17136, $this->getUser());
//        die;

        $categories_own_list = $results["categories"];
        $types_own_list = $results["types"];
        $prices_own_list = $results["prices"];
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();

        $user = $this->getUser();
        $userrepo= $em->getRepository('mycpBundle:user');
        $touroperators= array();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();

        $packageService = $this->get("mycp.partner.package.service");
        $isSpecialPackage = $packageService->isSpecialPackageFromAgency($travelAgency);
        $form = ($isSpecialPackage) ? $this->createForm(new paReservationExtendedType($this->get('translator'), $travelAgency)) : $this->createForm(new paReservationType($this->get('translator'), $travelAgency));

        $formFilterOwnerShip = $this->createForm(new FilterOwnershipType($this->get('translator'), array()));

        //proccess, pending, availability, notavailability, reserved, beaten, canceled, checkin
        $inAction = array();

        return $this->render('PartnerBundle:Backend:index.html.twig', array(
            "locale" => "es",
            "owns_categories" => null,
            "autocomplete_text_list" => null,
            "owns_prices" => $prices_own_list,
            "formFilterOwnerShip"=>$formFilterOwnerShip->createView(),
            'form'=>$form->createView(),
            'inAction'=>$inAction,
            "isSpecialPackage" => $isSpecialPackage
        ));
    }

    /**
     * @param Request $request
     */
    public function searchAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $filters= $request->get('requests_ownership_filter');
        $session->set("partner_arrival_date",$filters['arrival']);
        $session->set("partner_exit_date",$filters['exit']);
        $start=$request->request->get('start');
        $limit=$request->request->get('limit');

        $packageService = $this->get("mycp.partner.package.service");

        $filters["showOnlySelectedAccommodations"] = $packageService->isSpecialPackage();


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
        return new JsonResponse(array('response_twig'=>$response,'response_json'=>$result,'partner_arrival_date'=>$session->get("partner_arrival_date"),'partner_exit_date'=>$session->get("partner_exit_date")));
    }

    public function openReservationsListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();


        $user = $this->getUser();

        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));

        $packageService = $this->get("mycp.partner.package.service");
        $isSpecial = $packageService->isSpecialPackage();
        /*$list = $em->getRepository('PartnerBundle:paReservation')->getOpenReservationsList($tourOperator->getTravelAgency());

        $response = $this->renderView('PartnerBundle:Modal:open-reservations-list.html.twig', array(
            'list' => $list
        ));*/
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $ownership = $em->getRepository('mycpBundle:ownership')->find($request->get('accommodationId'));
        $session = $this->getRequest()->getSession();

        $locale = $this->get('translator')->getLocale();
        $currentServiceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
        $ownership_array=array();
        $ownership_array['own_id']=$ownership->getOwnId();
        $ownership_array['ownname']=$ownership->getOwnName();
        $ownership_array['OwnInmediateBooking2']=$ownership->isOwnInmediateBooking2();

        $ownership_array['breakfast']=$ownership->getOwnFacilitiesBreakfast();
        $priceBreakFast=explode('-',$ownership->getOwnFacilitiesBreakfastPrice());
        $ownership_array['breakfastPrice']=(count($priceBreakFast)==2)?$priceBreakFast[1]:$priceBreakFast[0];

        $ownership_array['dinner']=$ownership->getOwnFacilitiesDinner();
        $ownership_array['dinnerPrice']=$ownership->getOwnFacilitiesDinnerPriceTo();

        $response = $this->renderView('PartnerBundle:ownership:ownership_details_calendar.html.twig',array(
            'ownership'=>$ownership_array,
            'locale'=>$locale,'currentServiceFee'=>$currentServiceFee,
            'fromPartner' => true,
            'completePayment' => $tourOperator->getTravelAgency()->getAgencyPackages()[0]->getPackage()->getCompletePayment(),
            'comisionAgency' => $tourOperator->getTravelAgency()->getAgencyPackages()[0]->getPackage()->getId(),
            "isSpecial" => $isSpecial

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
        //$clientName = $request->get("clientName");
        $dateFrom = $request->get("dateFrom");
        $dateTo = $request->get("dateTo");
        $adults = $request->get("adults");
        $children = $request->get("children");
        //$clientId=$request->get("clientId");
        $accommodationId = $request->get("accommodationId");
        $roomType = $request->get("roomType");
        $roomsTotal = $request->get("roomsTotal");
        $reservationNumber = $request->get("reservationNumber");

        $client = array(
            "clientName" => $request->get("clientName"),
            "clientCountry" => $request->get("clientCountry"),
            "clientId" => $request->get("clientId"),
            //"clientBirthday" => $request->get("clientBirthday"),
            "clientComments" => $request->get("clientComments"),
        );
        //$clientEmail= $request->get("clientEmail");

        $dateFrom = Dates::createDateFromString($dateFrom,"/", 1);
        $dateTo = Dates::createDateFromString($dateTo,"/", 1);

        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $accommodation = $em->getRepository("mycpBundle:ownership")->find($accommodationId);

        $translator = $this->get('translator');
        $returnedObject = $em->getRepository("PartnerBundle:paReservation")->newReservation($travelAgency, $client, $adults, $children, $dateFrom, $dateTo, $accommodation, $user, $this->container, $translator,$reservationNumber, $roomType, $roomsTotal/*,$clientEmail*/);

        $list = $em->getRepository('PartnerBundle:paReservation')->getOpenReservationsList($travelAgency);

        $response = $this->renderView('PartnerBundle:Modal:open-reservations-list.html.twig', array(
            'list' => $list
        ));

        return new JsonResponse([
            'success' => true,
            'html' => $response,
            'message' => $returnedObject["message"]

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
        $completePayment = $travelAgency->getAgencyPackages()[0]->getPackage()->getCompletePayment();
        $reservation = $em->getRepository("PartnerBundle:paReservation")->find($id);

        //Pasar el paGeneralReservation a generalReservation
        $reservationDetails = $reservation->getDetails();


        //Configuration service send new availability check
        $service_email = $this->get('Email');
        $translator = $this->get('translator');

        //Send email user new availability check
        $subject = $translator->trans('subject.email.partner.new.availability.check', array(), "messages", strtolower($user->getUserLanguage()->getLangCode()));
        $content=$this->render('PartnerBundle:Mail:newAvailabilityCheck.html.twig', array(
            "reservations" => $reservationDetails,
            'user_locale'=> strtolower($user->getUserLanguage()->getLangCode()),
            'currency'=> strtoupper($user->getUserCurrency()->getCurrCode()),
            'currency_symbol'=>$user->getUserCurrency()->getCurrSymbol(),
            'currency_rate'=>$user->getUserCurrency()->getCurrCucChange()
        ));
        $service_email->sendTemplatedEmailPartner($subject, 'partner@mycasaparticular.com', $user->getUserEmail(), $content);


        $rooms=array();
        foreach($reservationDetails as $detail)
        {
            $paGeneralReservation = $detail->getOpenReservationDetail();
            $ownershipReservations=$paGeneralReservation->getPaOwnershipReservations();
            foreach($ownershipReservations as $ownreservation){
                $temp['roomType']=$ownreservation->getroomType();
                $temp['nights']=$ownreservation->getNights();
                $temp['adults']=$ownreservation->getAdults();
                $temp['children']=$ownreservation->getChildren();
                $temp['dateFrom']=$ownreservation->getDateFrom();
                $temp['dateTo']=$ownreservation->getDateTo();
                $rooms[]=$temp;
            }
        }
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $contacts=$travelAgency->getContacts();
        $phone_contact = (count($contacts)) ? $contacts[0]->getPhone() . ', ' . $contacts[0]->getMobile() : ' ';
        //Send email team reservations
        $content=$this->render('PartnerBundle:Mail:newAvailabilityCheckReservations.html.twig', array(
            "reservations" => $reservationDetails,
            "rooms"=> $rooms,
            'user_locale'=>'es',
            'currency'=> strtoupper($user->getUserCurrency()->getCurrCode()),
            'currency_symbol'=>$user->getUserCurrency()->getCurrSymbol(),
            'currency_rate'=>$user->getUserCurrency()->getCurrCucChange(),
            'travelAgency'=>$travelAgency,
            'agency_resp'=>(count($contacts))?$contacts[0]->getName():'',
            'phone_contact'=>$phone_contact
        ));
        $service_email->sendTemplatedEmailPartner($subject, 'partner@mycasaparticular.com', 'solicitud.partner@mycasaparticular.com', $content);

        foreach($reservationDetails as $detail)
        {

            $paGeneralReservation = $detail->getOpenReservationDetail(); // a eliminar
            $rooms[]=$paGeneralReservation->getPaOwnershipReservations();
            $paOwnershipReservations = $paGeneralReservation->getPaOwnershipReservations(); //a eliminar una a una

            $generalReservation = $paGeneralReservation->createReservation($completePayment);

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
            'list' => $list,
            'travelAgency'=>$travelAgency
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

        $canCreateReservation = $em->getRepository("PartnerBundle:paReservation")->canCreateReservation($reservation, $accommodation, $dateFrom, $dateTo);

        if(isset($reservation) && $canCreateReservation)
        {
            $adults = $reservation->getAdults();
            $children = $reservation->getChildren();
            //Actualizar total de ubicados
            $reservation->setAdultsWithAccommodation($reservation->getAdultsWithAccommodation() + $adults);
            $reservation->setChildrenWithAccommodation($reservation->getChildrenWithAccommodation() + $children);
            $em->persist($reservation);

            $translator = $this->get('translator');
            //Agregar un generalReservation por casa
            $returnedObject = $em->getRepository("PartnerBundle:paGeneralReservation")->createReservationForPartner($user, $accommodation, $dateFrom, $dateTo, $adults, $children, $this->container, $translator);

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
            'message' => $returnedObject["message"]

        ]);

    }

    public function detailedOpenReservationsListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /*$user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));*/
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

    public function deleteDetailedOpenReservationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $reservationDetailId = $request->get("idOpenReservationDetail");
        $paGeneralReservationId = $request->get("idPaGeneralReservation");
        $reservationNumber = $request->get("reservationNumber");
        $clientName = $request->get("clientName");
        $creationDate = $request->get("creationDate");
        $reservationDetail = $em->getRepository('PartnerBundle:paReservationDetail')->find($reservationDetailId);

        if(!isset($reservationDetail))
            return new JsonResponse([
                'success' => false,
                'html' => null,
                'message' => ""

            ]);

         $reservation = $reservationDetail->getReservation();

        $paGeneralReservation = $em->getRepository('PartnerBundle:paGeneralReservation')->find($paGeneralReservationId);
        $adults = 0;
        $children = 0;
        $deleteMainReservation = count($reservation->getDetails()) == 1;

        if(isset($paGeneralReservation))
        {
            foreach($paGeneralReservation->getPaOwnershipReservations() as $paOwnRes)
            {
                $adults = $paOwnRes->getAdults();
                $children = $paOwnRes->getChildren();
                $em->remove($paOwnRes);
            }

            $em->remove($paGeneralReservation);
        }

        $em->remove($reservationDetail);

        if($deleteMainReservation)
            $em->remove($reservation);
        else {
            $reservation->setAdultsWithAccommodation($reservation->getAdultsWithAccommodation() - $adults);
            $reservation->setChildrenWithAccommodation($reservation->getChildrenWithAccommodation() - $children);
            $em->persist($reservation);
        }
        $em->flush();

        $response = (!$deleteMainReservation) ? $this->renderView('PartnerBundle:Modal:detailed-open-reservations-list.html.twig', array(
            'reservation' => $reservation,
            'number' => $reservationNumber,
            'creationDate' => $creationDate,
            'clientName' => $clientName
        )) : "";

        $list = $em->getRepository('PartnerBundle:paReservation')->getOpenReservationsList($tourOperator->getTravelAgency());

        $responseReservations = $this->renderView('PartnerBundle:Modal:open-reservations-list.html.twig', array(
            'list' => $list
        ));

        return new JsonResponse([
            'success' => true,
            'html' => $response,
            'htmlReservations' => $responseReservations,
            'message' => ""

        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getClientAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('PartnerBundle:paClient')->find($request->get('idClient'));
        return new JsonResponse([
            'success' => true,
            'fullname'=>$client->getFullName(),
            'email'=>$client->getEmail(),
            'country' => ($client->getCountry() != null) ? $client->getCountry()->getCoId() : 0,
            //'birthday' => $client->getBirthdayDate()->format("Y-m-d"),
            "comments" => $client->getComments()
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function listClientAction(){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $clients= $em->getRepository('PartnerBundle:paClient')->findBy(array("travelAgency" => $travelAgency->getId()), array("fullname" => "ASC"));
        $data = array();
        foreach ($clients as $item) {
            $arrTmp = array();
            $arrTmp['id'] = $item->getId();
            $arrTmp['name'] = $item->getFullName();
            $data['aaData'][] = $arrTmp;
        }
        return new JsonResponse($data);
    }

    public function get_agency_namesAction($post) {
        $em = $this->getDoctrine()->getManager();
        $selected = '-1';
        if(isset($post['selected']) && $post['selected'] != "")
            $selected = $post['selected'];
        $data= $em->getRepository("PartnerBundle:paTravelAgency")->getAll($a='',$b='',$c='',$d='',$e='',$filter_package=3,$f='');
        $array= $data->getArrayResult();

        return $this->render('PartnerBundle:Search:agency_names.html.twig', array('selected' => $selected,'data'=>$array));
    }

}
