<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\generalReservation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;



class BackendReportController extends Controller
{
    function reportsAction()
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $existingCategories = $em->getRepository("mycpBundle:report")->getExistingReportCategories();

        return $this->render('mycpBundle:reports:report.html.twig', array(
            "categories" => $existingCategories
        ));
    }

    function dailyInPlaceClientsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $date = $request->get("dateParam");
        $errorText = "";
        $content = array();

        if($date == null || $date == "null")
        {
            $errorText = "Seleccione una fecha para generar el reporte";
        }
        else
        {
            $timer = $this->get('time');
            $dateRangeFrom = $timer->add("-30 days",$date, "Y-m-d");
            $dateRangeTo = $timer->add("+30 days",$date, "Y-m-d");

            $timer = $this->container->get("Time");

            $content = $em->getRepository("mycpBundle:report")->rpDailyInPlaceClients($date, $dateRangeFrom, $dateRangeTo, $timer);
        }

        return $this->render('mycpBundle:reports:dailyInPlaceClients.html.twig', array(
            'content' => $content,
            'errorText' => $errorText
        ));
    }

    public function dailyInPlaceClientsExcelAction($report, $date)
    {
        if($date != null && $date != "null" && $report != null && $report != "null")
        {
            $timer = $this->get('time');
            $dateRangeFrom = $timer->add("-30 days",$date, "Y-m-d");
            $dateRangeTo = $timer->add("+30 days",$date, "Y-m-d");



            $exporter = $this->get("mycp.service.export_to_excel");
            return $exporter->exportRpDailyInPlaceClients($date, $dateRangeFrom, $dateRangeTo, $report,$timer);
        }
        return null;
    }

   public function ownershipGeneralStatsAction(Request $request)
   {
       $em = $this->getDoctrine()->getManager();
       $provinceId = $request->get("filter_province");
       $munId = $request->get("filter_municipality");
       $province = null;
       $municipality = null;
        $errorText = "";

        $location='el país';
        if(($provinceId == null || $provinceId == "null")&&($munId == null || $munId == "null")){
            $resp=$em->getRepository('mycpBundle:ownershipStat')->getBulb();
        }
       else if($munId == null || $munId == "null"){
           $province = $em->getRepository('mycpBundle:province')->find($provinceId);
           $resp=$em->getRepository('mycpBundle:ownershipStat')->getBulb(null, $province,null);
           $location=$province->getProvName();
        }
       else{
            $municipality = $em->getRepository('mycpBundle:municipality')->find($munId);
           $province = $municipality->getMunProvId();
            $resp=$em->getRepository('mycpBundle:ownershipStat')->getBulb(null, null, $municipality);
            $location=$municipality->getMunName();

        }

        return $this->render('mycpBundle:reports:ownershipGeneralStats.html.twig', array(
            'content' => $resp,
            'location' => $location,
            'errorText' => $errorText,
            'province' => $province,
            'municipality' => $municipality
        ));
    }

    public function ownershipGeneralStatsLinkAction($nomenclator, $province, $municipality)
    {
        $em = $this->getDoctrine()->getManager();
        $nomenclator = $em->getRepository('mycpBundle:nomenclatorStat')->find($nomenclator);
        $province = $em->getRepository('mycpBundle:province')->find($province);
        $municipality = $em->getRepository('mycpBundle:municipality')->find($municipality);

        $reportListContent = $em->getRepository('mycpBundle:ownershipStat')->getOwnershipReportListContent($nomenclator, $province, $municipality);

        return $this->render('mycpBundle:reports:ownershipReportList.html.twig', array(
            'content' => $reportListContent,
            'nomenclator' => $nomenclator,
            'province' => $province,
            'municipality' => $municipality
        ));
    }

    public function ownershipGeneralStatsExcelAction(Request $request, $report, $province, $municipality)
    {
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportOwnershipGeneralStats($request, $report, $province, $municipality);
    }

    public function ownershipVsReservationsStatsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $provinceId = $request->get("filter_province");
        $munId = $request->get("filter_municipality");
        $desId = $request->get("filter_destination");
        $dateFrom = $request->get("dateRangeFrom");
        $dateTo = $request->get("dateRangeTo");
        $province = null;
        $municipality = null;
        $destination = null;
        $errorText = "";

        $location='el país';
        if(($provinceId == null || $provinceId == "null")&&($munId == null || $munId == "null")){
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getAccommodations(null, null,null, null, $dateFrom, $dateTo);
        }
        else if($desId != null && $desId != "null" && $desId > 0){
            $destination = $em->getRepository('mycpBundle:destination')->find($desId);
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getAccommodations(null, null,null, $destination, $dateFrom, $dateTo);
            $location=$destination->getDesName();
        }
        else if($munId == null || $munId == "null"){
            $province = $em->getRepository('mycpBundle:province')->find($provinceId);
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getAccommodations(null, $province,null, null, $dateFrom, $dateTo);
            $location=$province->getProvName();
        }
        else{
            $municipality = $em->getRepository('mycpBundle:municipality')->find($munId);
            $province = $municipality->getMunProvId();
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getAccommodations(null, null, $municipality, null, $dateFrom, $dateTo);
            $location=$municipality->getMunName();

        }

        return $this->render('mycpBundle:reports:ownershipReservationStats.html.twig', array(
            'content' => $resp,
            'location' => $location,
            'errorText' => $errorText,
            'province' => $province,
            'municipality' => $municipality,
            'destination' => $destination,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ));
    }

    public function ownershipVsReservationsStatsTotalAction($filter_province, $filter_municipality, $filter_destination, $dateFrom, $dateTo)
{
    $em = $this->getDoctrine()->getManager();
    $province = null;
    $municipality = null;
    $destination = null;
    $errorText = "";

    $location='el país';
    if(($filter_province == null || $filter_province == "null")&&($filter_municipality == null || $filter_municipality == "null")){
        $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, null,null, null, $dateFrom, $dateTo);
    }
    else if($filter_destination != null && $filter_destination != "null"){
        $destination = $em->getRepository('mycpBundle:destination')->find($filter_destination);
        $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, null,null, $destination, $dateFrom, $dateTo);
        $location=$destination->getDesName();
    }
    else if($filter_municipality == null || $filter_municipality == "null"){
        $province = $em->getRepository('mycpBundle:province')->find($filter_province);
        $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, $province,null, null, $dateFrom, $dateTo);
        $location=$province->getProvName();
    }
    else{
        $municipality = $em->getRepository('mycpBundle:municipality')->find($filter_municipality);
        $province = $municipality->getMunProvId();
        $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, null, $municipality, null, $dateFrom, $dateTo);
        $location=$municipality->getMunName();

    }

    $content = $this->renderView('mycpBundle:reports:ownershipReservationStatsTotal.html.twig', array(
        'content' => $resp,
        'location' => $location,
        'errorText' => $errorText,
        'province' => $province,
        'municipality' => $municipality,
        'destination' => $destination,
    ));

    return new Response($content, 200);
}

    public function ownershipVsReservationsStatsAccommodationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $filter_province = $request->get("filter_province");
        $filter_municipality = $request->get("filter_municipality");
        $filter_destination = $request->get("filter_destination");
        $dateFrom = $request->get("dateFrom");
        $dateTo = $request->get("dateTo");
        $ownership = $request->get("ownership");
        $province = null;
        $municipality = null;
        $destination = null;
        $errorText = "";

        $location='el país';
        if(($filter_province == null || $filter_province == "null")&&($filter_municipality == null || $filter_municipality == "null")){
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, null,null, null, $dateFrom, $dateTo, $ownership);
        }
        else if($filter_destination != null && $filter_destination != "null"){
            $destination = $em->getRepository('mycpBundle:destination')->find($filter_destination);
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, null,null, $destination, $dateFrom, $dateTo, $ownership);
            $location=$destination->getDesName();
        }
        else if($filter_municipality == null || $filter_municipality == "null"){
            $province = $em->getRepository('mycpBundle:province')->find($filter_province);
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, $province,null, null, $dateFrom, $dateTo, $ownership);
            $location=$province->getProvName();
        }
        else{
            $municipality = $em->getRepository('mycpBundle:municipality')->find($filter_municipality);
            $province = $municipality->getMunProvId();
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, null, $municipality, null, $dateFrom, $dateTo, $ownership);
            $location=$municipality->getMunName();

        }

        $content = $this->renderView('mycpBundle:reports:ownershipReservationStatsTotal.html.twig', array(
            'content' => $resp,
            'location' => $location,
            'errorText' => $errorText,
            'province' => $province,
            'municipality' => $municipality,
            'destination' => $destination,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ));

        return new Response($content, 200);
    }

    public function ownershipVsReservationsStatsExcelAction(Request $request,$report, $from_date, $to_date, $province, $municipality, $destination)
    {
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportOwnershipVsReservationsStats($request, $report, $from_date, $to_date, $province, $municipality, $destination);
    }

    public function getByCategoryCallbackAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $request->get("category");
        $reports = $em->getRepository('mycpBundle:report')->findBy(array('report_category'=>$category, "published"=> true));
        $reportContent =  $this->renderView('mycpBundle:utils:listReports.html.twig', array('reports' => $reports));
        return new Response($reportContent, 200);
    }

    public function getParametersCallbackAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $reportId = $request->get("report");
        $report = $em->getRepository("mycpBundle:report")->find($reportId);
        $parameters = $em->getRepository('mycpBundle:reportParameter')->findBy(array('parameter_report'=>$reportId), array("parameter_order"=>"ASC"));
        $content =  $this->renderView('mycpBundle:reports:parameters.html.twig', array('report' => $report, 'parameters' => $parameters));
        return new Response($content, 200);
    }

    public function ownershipGeneralListExcelAction($nomenclator, $municipality, $province)
    {
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportOwnershipGeneralList($nomenclator, $province, $municipality);
    }

    public function ownershipSalesReportAction(){
        $conn=$this->get('doctrine.dbal.default_connection');
                $query="SELECT
  own.own_mcp_code as codigo,
  own.own_name as nombre,
  own.own_homeowner_1 as propietario1,
  own.own_homeowner_2 as propietario2,
  own.own_email_1 as correo1,
  own.own_email_2 as correo2,
  own.own_phone_number AS telefono,
  own.own_phone_code as codigo_telefono,
  own.own_mobile_number as celular,
  own.own_address_street as calle,
  own.own_address_number as numero,
  own.own_address_between_street_1 as entre,
  own.own_address_between_street_2 as y,
  municipality.mun_name as municipio,
  province.prov_name as provincia,
  own.own_saler as gestor,
  own.own_creation_date as creada,
  (SELECT COUNT(ownershipreservation.own_res_id) FROM ownershipreservation INNER JOIN generalreservation ON generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id WHERE generalreservation.gen_res_own_id=own.own_id) AS solicitudes,
  (SELECT COUNT(ownershipreservation.own_res_id) FROM ownershipreservation INNER JOIN generalreservation ON generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id WHERE generalreservation.gen_res_own_id=own.own_id AND ownershipreservation.own_res_status=5) AS reservas,
  (SELECT SUM(ownershipreservation.own_res_total_in_site) FROM ownershipreservation INNER JOIN generalreservation ON generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id WHERE generalreservation.gen_res_own_id=own.own_id AND ownershipreservation.own_res_status=5) AS ingresos,
  own.own_type as tipo,
  own.own_selection as seleccion,
  own.own_rooms_total as habitaciones,
  own.own_minimum_price as precio_baja,
  own.own_maximum_price as precio_alta,
  own.own_commission_percent as porciento_acordado,
  own.own_comments_total as reviews,
  own.own_category as categoria,
  (SELECT COUNT(ownershipphoto.own_pho_id) FROM ownershipphoto INNER JOIN ownership ON ownership.own_id = ownershipphoto.own_pho_own_id WHERE ownershipphoto.own_pho_own_id=own.own_id) AS fotos,
  (SELECT COUNT(usercasa.user_casa_id) FROM usercasa INNER JOIN ownership ON ownership.own_id = usercasa.user_casa_ownership WHERE usercasa.user_casa_ownership=own.own_id) AS modulo_casa,
  ownershipstatus.status_name as estado
FROM ownership own
  INNER JOIN municipality ON municipality.mun_id = own.own_address_municipality
  INNER JOIN province ON province.prov_id = municipality.mun_prov_id
  INNER JOIN ownershipstatus ON ownershipstatus.status_id = own.own_status
ORDER BY own.own_mcp_code ASC
;";

        $stmt=$conn->prepare($query);
        $stmt->execute();
       $result=$stmt->fetchAll();
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->createExcelForSalesReports($result);
    }


    public function reservationsByClientReportAction(Request $request, $from_date, $to_date){
        $em = $this->getDoctrine()->getManager();
        $dateRangeFrom = $request->get("dateRangeFrom");
        $dateRangeTo = $request->get("dateRangeTo");
        $errorText = "";
        $content = array();

        if($dateRangeFrom == null || $dateRangeFrom == ""||$dateRangeTo == null || $dateRangeTo == "")
        {
            $errorText = "Seleccione un rango de fechas para generar el reporte";
        }
        else{
            $dateFrom=\DateTime::createFromFormat('Y-m-d',$dateRangeFrom);
            $dateTo=\DateTime::createFromFormat('Y-m-d', $dateRangeTo);
            $content = $em->getRepository("mycpBundle:report")->reservationsByClientsSummary($dateFrom, $dateTo);
        }

        return $this->render('mycpBundle:reports:reservationsByClientsSummary.html.twig', array(
            'content' => $content,
            'errorText' => $errorText,
            'dateTo' => $dateRangeTo,
            'dateFrom' => $dateRangeFrom
        ));
    }
    public function bookingSummaryReportAction(Request $request, $from_date, $to_date){
        $em = $this->getDoctrine()->getManager();
        $dateRangeFrom = $request->get("dateRangeFrom");
        $dateRangeTo = $request->get("dateRangeTo");
        $errorText = "";
        $content = array();

        if($dateRangeFrom == null || $dateRangeFrom == ""||$dateRangeTo == null || $dateRangeTo == "")
        {
            $errorText = "Seleccione un rango de fechas para generar el reporte";
        }
        else{
            $dateFrom=\DateTime::createFromFormat('Y-m-d',$dateRangeFrom);
            $dateTo=\DateTime::createFromFormat('Y-m-d', $dateRangeTo);
//            $content = $em->getRepository("mycpBundle:report")->bookingsSummary($dateFrom, $dateTo);
            $bookings = $em->getRepository('mycpBundle:generalReservation')
                ->getAllBookingsReport('', $dateFrom->format('d_m_Y'), '', $dateTo->format('d_m_Y'), '', '', '');

        }
        return $this->render('mycpBundle:reports:bookinsSummary.html.twig', array(
            'content' => count($bookings),
            'errorText' => $errorText,
            'dateTo' => $dateRangeTo,
            'dateFrom' => $dateRangeFrom
        ));
    }
    public function reservationsByClientReportExcelAction(Request $request,$report, $from_date, $to_date){
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportReservationsByClientReport($request, $report, $from_date, $to_date);
    }
    public function bookingsSummaryReportExcelAction(Request $request,$report, $from_date, $to_date){
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportReservationsByClientReport($request, $report, $from_date, $to_date);
    }

    public function list_reservations_bookingAction(Request $request) {
        $filter_booking_number = $request->get('filter_booking_number');
        $filter_date_booking_from = $request->get('filter_date_booking_from');
        $filter_date_booking_to = $request->get('filter_date_booking_to');
        $filter_user_booking = $request->get('filter_user_booking');
        $filter_reservation = $request->get("filter_reservation");
        $filter_ownership = $request->get("filter_ownership");
        $filter_currency = $request->get("filter_currency");

        if ($request->getMethod() == 'POST' && $filter_booking_number == 'null' && $filter_date_booking_from == 'null' &&$filter_date_booking_to == 'null' && $filter_user_booking == 'null' && $filter_reservation == 'null' && $filter_ownership == 'null' && $filter_currency == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_report_reservations_booking'));
        }

        if ($filter_booking_number == 'null')
            $filter_booking_number = '';
        if ($filter_date_booking_from == 'null')
            $filter_date_booking_from = '';
        if ($filter_date_booking_to == 'null')
            $filter_date_booking_to = '';
        if ($filter_user_booking == 'null')
            $filter_user_booking = '';
        if ($filter_reservation == 'null')
            $filter_reservation = '';
        if ($filter_currency == 'null')
            $filter_currency = '';

        if ($filter_ownership == 'null')
            $filter_ownership = '';
        $em = $this->getDoctrine()->getManager();
        $bookings = $em->getRepository('mycpBundle:generalReservation')
            ->getAllBookingsReport($filter_booking_number, $filter_date_booking_from, $filter_user_booking, $filter_date_booking_to, $filter_reservation, $filter_ownership, $filter_currency);
        $filter_date_booking_from = str_replace('_', '/', $filter_date_booking_from);
        $filter_date_booking_to = str_replace('_', '/', $filter_date_booking_to);

       return $this->render('mycpBundle:reports:list_booking.html.twig', array(
            "bookings" => $bookings,
            'filter_booking_number' => $filter_booking_number,
            'filter_date_booking_from' => $filter_date_booking_from,
            'filter_date_booking_to' => $filter_date_booking_to,
            'filter_user_booking' => $filter_user_booking,
            'filter_reservation' => $filter_reservation,
            'filter_ownership' => $filter_ownership,
            'filter_currency' => $filter_currency,
            'total_items' => count($bookings),
        ));
    }
    public function list_reservations_booking_to_excelAction(Request $request) {
        $filter_booking_number = $request->get('filter_booking_number');
        $filter_date_booking_from = $request->get('filter_date_booking_from');
        $filter_date_booking_to = $request->get('filter_date_booking_to');
        $filter_user_booking = $request->get('filter_user_booking');
        $filter_reservation = $request->get("filter_reservation");
        $filter_ownership = $request->get("filter_ownership");
        $filter_currency = $request->get("filter_currency");

        if ($request->getMethod() == 'POST' && $filter_booking_number == 'null' && $filter_date_booking_from == 'null' &&$filter_date_booking_to == 'null' && $filter_user_booking == 'null' && $filter_reservation == 'null' && $filter_ownership == 'null' && $filter_currency == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_report_reservations_booking'));
        }

        if ($filter_booking_number == 'null')
            $filter_booking_number = '';
        if ($filter_date_booking_from == 'null')
            $filter_date_booking_from = '';
        if ($filter_date_booking_to == 'null')
            $filter_date_booking_to = '';
        if ($filter_user_booking == 'null')
            $filter_user_booking = '';
        if ($filter_reservation == 'null')
            $filter_reservation = '';
        if ($filter_currency == 'null')
            $filter_currency = '';

        if ($filter_ownership == 'null')
            $filter_ownership = '';
        $em = $this->getDoctrine()->getManager();
        $bookings = $em->getRepository('mycpBundle:generalReservation')
            ->getAllBookingsReport($filter_booking_number, $filter_date_booking_from, $filter_user_booking, $filter_date_booking_to, $filter_reservation, $filter_ownership, $filter_currency);
        $dataArr=array();
        foreach ($bookings as $content) {
            $data = array();

            $data[0] = $content["booking_id"];
            $data[1] = $content["created"]->format('d/m/Y');
            $data[2] = $content["payed_amount"].' '.$content['curr_code'];
            $data[3] = $content["curr_code"];
            $data[4] = $content["booking_user_dates"];
            $data[5] = $content["country"];
            $data[6] = $content["reservationCode"];
            $data[7] = $content["ownCode"];
            array_push($dataArr, $data);
        }
        $filter_date_booking_from = str_replace('_', '/', $filter_date_booking_from);
        $filter_date_booking_to = str_replace('_', '/', $filter_date_booking_to);
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportBookingDetailsReport($dataArr, $filter_date_booking_from, $filter_date_booking_from);

    }

    public function details_client_reservationAction($id_client,Request $request) {

//        {filter_date_from}/{filter_date_to}/{filter_reservation_status}/{filter_province}/{filter_destination}/{filter_nights}
        $filter_date_from = $request->get('filter_date_from');
        $filter_date_to = $request->get('filter_date_to');
        $filter_reservation_status = $request->get('filter_reservation_status');
        $filter_province = $request->get('filter_province');
        $filter_destination = $request->get("filter_destination");
        $filter_nights = $request->get("filter_nights");
        $service_time = $this->get('time');

        if ($filter_date_from == 'null')
            $filter_date_from = '';
        if ($filter_date_to == 'null')
            $filter_date_to = '';
        if ($filter_reservation_status == 'null')
            $filter_reservation_status = '';
        if ($filter_province == 'null')
            $filter_province = '';
        if ($filter_destination == 'null')
            $filter_destination = '';
        if ($filter_nights == 'null')
            $filter_nights = '';
        $dateFrom=\DateTime::createFromFormat('d-m-Y',$filter_date_from);
        $dateTo=\DateTime::createFromFormat('d-m-Y', $filter_date_to);
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('mycpBundle:user')->find($id_client);
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $id_client));
        $reservations = $em->getRepository('mycpBundle:generalReservation')->getUserReservationsFiltered($id_client, $dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d'), $filter_reservation_status, $filter_province, $filter_destination, $filter_nights);
        $price = 0;
        return $this->render('mycpBundle:reports:reservationDetailsClient.html.twig', array(
            'reservations' => $reservations,
            'client' => $client,
            'errors' => '',
            'filter_date_from'=>$dateFrom,
            'filter_date_to'=>$dateTo,
            'filter_reservation_status'=>$filter_reservation_status,
            'filter_province'=>$filter_province,
            'filter_destination'=>$filter_destination,
            'filter_nights'=>$filter_nights,
            'tourist' => $userTourist[0]
        ));
    }
    public function details_client_reservation_to_excelAction($id_client, Request $request) {

        $filter_date_from = $request->get('filter_date_from');
        $filter_date_to = $request->get('filter_date_to');
        $filter_reservation_status = $request->get('filter_reservation_status');
        $filter_province = $request->get('filter_province');
        $filter_destination = $request->get("filter_destination");
        $filter_nights = $request->get("filter_nights");
        $service_time = $this->get('time');

        if ($filter_date_from == 'null')
            $filter_date_from = '';
        if ($filter_date_to == 'null')
            $filter_date_to = '';
        if ($filter_reservation_status == 'null')
            $filter_reservation_status = '';
        if ($filter_province == 'null')
            $filter_province = '';
        if ($filter_destination == 'null')
            $filter_destination = '';
        if ($filter_nights == 'null')
            $filter_nights = '';
        $dateFrom=\DateTime::createFromFormat('d-m-Y',$filter_date_from);
        $dateTo=\DateTime::createFromFormat('d-m-Y', $filter_date_to);
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('mycpBundle:user')->find($id_client);
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $id_client));
        $reservations = $em->getRepository('mycpBundle:generalReservation')->getUserReservationsFiltered($id_client, $dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d'),$filter_reservation_status, $filter_province, $filter_destination, $filter_nights);
        $price = 0;
        $dataArr=array();
         foreach ($reservations as $content) {
            $data = array();

            $data[0] = $content["gen_res_date"]->format('d/m/Y');
            $data[1] = "CAS.".$content["gen_res_id"];
            $data[2] = $content["own_mcp_code"];
            $data[3] = $content["rooms"];
            $data[4] = $content["adults"];
            $data[5] = $content["childrens"];
            $data[6] = $content["totalNights"];
            $data[7] = $content["gen_res_total_in_site"].' CUC';
            $data[8] = $content["des_name"];
             $estado='';
             switch($content["gen_res_status"]){
                 case generalReservation::STATUS_AVAILABLE: $estado='Disponible';
                     break;
                 case generalReservation::STATUS_CANCELLED: $estado='Cancelada';
                     break;
                 case generalReservation::STATUS_PARTIAL_CANCELLED: $estado='Parcialmente Cancelada';
                     break;
                 case generalReservation::STATUS_NOT_AVAILABLE: $estado='No disponible';
                     break;
                 case generalReservation::STATUS_PENDING: $estado='Pendiente';
                     break;
                 case generalReservation::STATUS_RESERVED: $estado='Reservada';
                     break;
                 case generalReservation::STATUS_PARTIAL_RESERVED: $estado='Parcialmente Reservada';
                     break;
                 case generalReservation::STATUS_PARTIAL_RESERVED: $estado='Parcialmente Reservada';
                     break;
                 case generalReservation::STATUS_OUTDATED: $estado='Vencida';
                     break;
             }
            $data[9] = $estado;
            array_push($dataArr, $data);
        }
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportdetails_client_reservation_to_excelReport($dataArr, $dateFrom, $dateTo, $client);


    }

    public function reservationRangeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dateFrom = $request->get("dateRangeFrom");
        $dateTo = $request->get("dateRangeTo");

        $list = $em->getRepository("mycpBundle:generalReservation")->getReservationRangeReportContent($dateFrom, $dateTo);

        return $this->render('mycpBundle:reports:reservationRangeReport.html.twig', array(
            'list' => $list,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ));
    }

    public function reservationRangeExcelAction(Request $request,$report, $from_date, $to_date)
    {
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportReservationRange($request, $report, $from_date, $to_date);
    }

    public function reservationRangeDetailsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $filter_user = $request->get('filter_user');
        $filter_date_from = $request->get('from_date');
        $filter_date_to = $request->get('to_date');
        $filter_province = $request->get('filter_province');
        $filter_destination = $request->get("filter_destination");
        $filter_nights = $request->get("filter_nights");
        $reservation_status = $request->get("reservation_status");

        if ($filter_date_from == 'null')
            $filter_date_from = '';
        if ($filter_date_to == 'null')
            $filter_date_to = '';
        if ($reservation_status == 'null')
            $reservation_status = '';
        if ($filter_province == 'null')
            $filter_province = '';
        if ($filter_destination == 'null')
            $filter_destination = '';
        if ($filter_nights == 'null')
            $filter_nights = '';
        if ($filter_user == 'null')
            $filter_user = '';

        $list = $em->getRepository("mycpBundle:generalReservation")->getReservationRangeDetailReportContent($reservation_status,$filter_date_from, $filter_date_to,$filter_nights, $filter_province, $filter_destination, $filter_user);

        return $this->render('mycpBundle:reports:reservationRangeDetailsReport.html.twig', array(
            'list' => $list,
            'dateFrom' => $filter_date_from,
            'dateTo' => $filter_date_to,
            'reservationStatus' => $reservation_status,
            'filter_nights' => $filter_nights,
            "filter_province" => $filter_province,
            "filter_destination" => $filter_destination,
            "filter_user" => $filter_user
        ));
    }

}
