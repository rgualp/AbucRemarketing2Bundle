<?php

namespace MyCp\mycpBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MyCp\FrontEndBundle\Helpers\ReservationHelper;
use MyCp\FrontEndBundle\Helpers\Time;
use MyCp\FrontEndBundle\Helpers\Utils;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\unavailabilityDetails;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\Notifications;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GeneralReservationService extends Controller
{
    /**
     * @var ObjectManager
     */
    private $em;

    private $timer;

    protected $tripleRoomCharge;

    private $calendarService;

    private $logger;

    protected $container;

    public function __construct(ObjectManager $em, Time $timer, $tripleRoomCharge, $calendarService, $logger, $container)
    {
        $this->em = $em;
        $this->timer = $timer;
        $this->tripleRoomCharge = $tripleRoomCharge;
        $this->calendarService = $calendarService;
        $this->logger = $logger;
        $this->container = $container;
    }

    public function createAvailableOfferFromRequest($request, user $user, $attendedDate = null)
    {
        return $this->createOfferFromRequest($request, $user, generalReservation::STATUS_AVAILABLE, ownershipReservation::STATUS_AVAILABLE, null, $attendedDate);
    }

    public function createReservedOfferFromRequest($request, user $user, booking $booking)
    {
        return $this->createOfferFromRequest($request, $user, generalReservation::STATUS_RESERVED, ownershipReservation::STATUS_RESERVED, $booking);
    }

    private function createOfferFromRequest($request, user $user, $generalReservationStatus, $ownReservationsStatus, booking $booking = null, $attendedDate = null)
    {
        $id_ownership = $request->get('data_ownership');
        $reservations = array();

        $ownership = $this->em->getRepository('mycpBundle:ownership')->find($id_ownership);

        if (!$request->get('data_reservation'))
            throw $this->createNotFoundException();
        $data = $request->get('data_reservation');

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

        $general_reservation = new generalReservation();
        $general_reservation->setGenResUserId($user);

        if($attendedDate == null or $attendedDate == "")
            $general_reservation->setGenResDate(new \DateTime(date('Y-m-d')));
        else
            $general_reservation->setGenResDate(\DateTime::createFromFormat("Y-m-d", $attendedDate));

        $general_reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
        $general_reservation->setGenResHour(date('G'));
        $general_reservation->setGenResStatus($generalReservationStatus);
        $general_reservation->setGenResFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
        $general_reservation->setGenResToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));
        $general_reservation->setGenResSaved(0);
        $general_reservation->setGenResOwnId($ownership);
        $general_reservation->setModified(new \DateTime());
        $general_reservation->setModifiedBy($this->getLoggedUserId());
        $currentServiceFee = $this->em->getRepository("mycpBundle:serviceFee")->getCurrent();
        $general_reservation->setServiceFee($currentServiceFee);
        $this->em->persist($general_reservation);

        $total_price = 0;
        $destination_id = ($ownership->getOwnDestination() != null) ? $ownership->getOwnDestination()->getDesId() : null;
        $count_adults = 0;
        $count_children = 0;
        $totalNights = 0;
        $partialTotalNights = 0;

        for ($i = 0; $i < count($array_ids_rooms); $i++) {
            $room = $this->em->getRepository('mycpBundle:room')->find($array_ids_rooms[$i]);
            $count_adults = (isset($array_count_kids[$i])) ? $array_count_guests[$i] : 1;
            $count_children = (isset($array_count_kids[$i])) ? $array_count_kids[$i] : 0;

            $array_dates = $this->timer->datesBetween($start_timestamp, $end_timestamp);
            $partialTotalNights = $this->timer->nights($start_timestamp, $end_timestamp);
            $totalNights += $partialTotalNights;
            $temp_price = 0;
            $triple_room_recharge = ($room->isTriple() && $count_adults + $count_children >= 3) ? $this->tripleRoomCharge : 0;
            $seasons = $this->em->getRepository("mycpBundle:season")->getSeasons($start_timestamp, $end_timestamp, $destination_id);
            for ($a = 0; $a < count($array_dates) - 1; $a++) {
                $seasonType = $this->timer->seasonTypeByDate($seasons, $array_dates[$a]);
                $roomPrice = $room->getPriceBySeasonType($seasonType);
                $total_price += $roomPrice + $triple_room_recharge;
                $temp_price += $roomPrice + $triple_room_recharge;
            }

            $ownership_reservation = new ownershipReservation();
            $ownership_reservation->setOwnResCountAdults($count_adults);
            $ownership_reservation->setOwnResCountChildrens($count_children);
            $ownership_reservation->setOwnResNightPrice(0);
            $ownership_reservation->setOwnResStatus($ownReservationsStatus);
            $ownership_reservation->setOwnResReservationFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
            $ownership_reservation->setOwnResReservationToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));
            $ownership_reservation->setOwnResSelectedRoomId($room);
            $ownership_reservation->setOwnResRoomPriceDown($room->getRoomPriceDownTo());
            $ownership_reservation->setOwnResRoomPriceUp($room->getRoomPriceUpTo());
            $specialPrice = ($room->getRoomPriceSpecial() != null && $room->getRoomPriceSpecial() > 0) ? $room->getRoomPriceSpecial() : $room->getRoomPriceUpTo();
            $ownership_reservation->setOwnResRoomPriceSpecial($specialPrice);
            $ownership_reservation->setOwnResGenResId($general_reservation);
            $ownership_reservation->setOwnResRoomType($room->getRoomType());
            $ownership_reservation->setOwnResTotalInSite($temp_price);
            $ownership_reservation->setOwnResReservationBooking($booking);
            $ownership_reservation->setOwnResNights($partialTotalNights);
            $general_reservation->setGenResTotalInSite($total_price);
            $general_reservation->setGenResSaved(1);
            $this->em->persist($ownership_reservation);
            array_push($reservations, $ownership_reservation);
        }

        $general_reservation->setGenResNights($totalNights);
        $this->em->persist($general_reservation);
        $this->em->flush();

        $nights = array();
        foreach ($reservations as $nReservation) {
            $nights[$nReservation->getOwnResId()] = count($array_dates) - 1;
        }

        $general_reservation->setGenResNights($totalNights);
        $this->em->persist($general_reservation);
        $this->em->flush();



        return array('reservations' => $reservations, 'nights' => $nights, 'generalReservation' => $general_reservation);
    }

    public function updateReservationFromRequest($post, $reservation, $ownership_reservations)
    {
        $process = $this->processPost($post);
        $errors = $process["errors"];
        $details_total = $process["detailsTotal"];
        $available_total = $process["available"];
        $non_available_total = $process["nonAvailable"];
        $cancelled_total = $process["cancelled"];
        $outdated_total = $process["outdated"];

        if (count($errors) == 0) {
            $totalPrice = 0;
            $nights = 0;
            foreach ($ownership_reservations as $ownership_reservation) {
                $start = explode('/', $post['date_from_' . $ownership_reservation->getOwnResId()]);
                $end = explode('/', $post['date_to_' . $ownership_reservation->getOwnResId()]);
                $start_timestamp = mktime(0, 0, 0, $start[1], $start[0], $start[2]);
                $end_timestamp = mktime(0, 0, 0, $end[1], $end[0], $end[2]);

                $ownership_reservation->setOwnResReservationFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
                $ownership_reservation->setOwnResReservationToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));

                if (isset($post['service_room_price_' . $ownership_reservation->getOwnResId()]) && $post['service_room_price_' . $ownership_reservation->getOwnResId()] != "" && $post['service_room_price_' . $ownership_reservation->getOwnResId()] != "0") {
                    $ownership_reservation->setOwnResNightPrice($post['service_room_price_' . $ownership_reservation->getOwnResId()]);
                }
                else
                    $ownership_reservation->setOwnResNightPrice(0);


                $ownership_reservation->setOwnResCountAdults($post['service_room_count_adults_' . $ownership_reservation->getOwnResId()]);
                $ownership_reservation->setOwnResCountChildrens($post['service_room_count_childrens_' . $ownership_reservation->getOwnResId()]);
                $ownership_reservation->setOwnResStatus($post['service_own_res_status_' . $ownership_reservation->getOwnResId()]);

                $partialTotalPrice = ReservationHelper::getTotalPrice($this->em, $this->timer, $ownership_reservation, $this->tripleRoomCharge);

                $totalPrice+=$partialTotalPrice;
                $ownership_reservation->setOwnResTotalInSite($partialTotalPrice);

                $partialNights = $this->timer->nights($start_timestamp, $end_timestamp);
                $nights+=$partialNights;
                $ownership_reservation->setOwnResNights($partialNights);

                if ($post['service_own_res_status_' . $ownership_reservation->getOwnResId()] == ownershipReservation::STATUS_RESERVED) {
                    $this->updateICal($ownership_reservation->getOwnResSelectedRoomId());
                }

                $this->em->persist($ownership_reservation);

                $createUnavailabilityDetails = (isset($post['updateCalendar_'. $ownership_reservation->getOwnResId()]) && !empty($post['updateCalendar_'. $ownership_reservation->getOwnResId()]));

                if($createUnavailabilityDetails) {
                    //Crear una no disponibilidad si la reservacion se marca como No Disponible
                    if ($ownership_reservation->getOwnResStatus() == ownershipReservation::STATUS_NOT_AVAILABLE && $this->em->getRepository("mycpBundle:unavailabilityDetails")->existByDateAndRoom($ownership_reservation->getOwnResSelectedRoomId(), $ownership_reservation->getOwnResReservationFromDate(), $ownership_reservation->getOwnResReservationToDate()) == 0) {
                        $uDetails = new unavailabilityDetails();
                        $room = $this->em->getRepository("mycpBundle:room")->find($ownership_reservation->getOwnResSelectedRoomId());
                        $uDetails->setRoom($room)
                            ->setUdSyncSt(SyncStatuses::ADDED)
                            ->setUdFromDate($ownership_reservation->getOwnResReservationFromDate())
                            ->setUdToDate($ownership_reservation->getOwnResReservationToDate())
                            ->setUdReason("Generada automaticamente por ser no disponible la reserva CAS." . $ownership_reservation->getOwnResGenResId()->getGenResId());

                        $this->em->persist($uDetails);
                    }
                }

            }


            $reservation->setGenResTotalInSite($totalPrice);
            $reservation->setGenResSaved(1);
            $reservation->setGenResNights($nights);
            $reservation->setModified(new \DateTime());
            $reservation->setModifiedBy($this->getLoggedUserId());
            $send_notification = false;
            if ($reservation->getGenResStatus() != generalReservation::STATUS_RESERVED) {
                if ($non_available_total > 0 && $non_available_total == $details_total) {
                    $status = generalReservation::STATUS_NOT_AVAILABLE;
                    $reservation->setGenResStatus(generalReservation::STATUS_NOT_AVAILABLE);
                    $reservation->setCurrentResponseTime();
                    $reservation->setGenResStatusDate(new \DateTime());

                    //Enviar oferta con 3 casas de reserva inmediata
                    if($reservation->getGenResUserId()->getRoles()!='ROLE_CLIENT_PARTNER'&&$reservation->getGenResUserId()->getRoles()!='ROLE_CLIENT_PARTNER_TOUROPERATOR'&&$reservation->getGenResUserId()->getRoles()!='ROLE_ECONOMY_PARTNER') {
                        $service_email = $this->container->get('Email');
                        $emailManager = $this->container->get('mycp.service.email_manager');


                        $user_tourist = $this->em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $reservation->getGenResUserId()));

                        if ($user_tourist == null) {
                            $user_tourist = $this->em->getRepository('mycpBundle:user')->find($reservation->getGenResUserId());
                            $userLocale = strtolower($user_tourist->getUserLanguage()->getLangCode());
                        } else {
                            $userLocale = strtolower($user_tourist->getUserTouristLanguage()->getLangCode());
                        }

                        $ownership = $this->em->getRepository('mycpBundle:ownership')->find($reservation->getGenResOwnId()->getOwnId());

                        $owns_in_destination = $this->em->getRepository("mycpBundle:ownership")->getRecommendableAccommodations($reservation->getGenResFromDate(), $reservation->getGenResToDate(), $ownership->getOwnMinimumPrice(), $ownership->getOwnAddressMunicipality()->getMunId(), $ownership->getOwnAddressProvince()->getProvId(), 3, $ownership->getOwnId(), $reservation->getGenResUserId()->getUserId(), null, true);

                        $emailBody = $emailManager->getViewContent('FrontEndBundle:mails:sendOffer.html.twig',
                            array('user' => $reservation->getGenResUserId(),
                                'owns_in_destination' => $owns_in_destination,
                                'user_locale' => $userLocale));

                        $subject = $this->get('translator')->trans(
                            'NEW_OFFER_TOURIST_SUBJECT',
                            array(),
                            'messages',
                            $userLocale
                        );
                        $service_email->sendEmail($subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com', $reservation->getGenResUserId()->getUserEmail(), $emailBody);
                    }
                } else if ($available_total > 0 && $available_total == $details_total) {
                    $status = generalReservation::STATUS_AVAILABLE;

                    /*if($reservation->getGenResStatus() == generalReservation::STATUS_OUTDATED)
                        $reservation->setGenResDate(new \DateTime());*/

                    $reservation->setGenResStatus(generalReservation::STATUS_AVAILABLE);
                    $send_notification = true;
                    $reservation->setCurrentResponseTime();
                    $reservation->setGenResStatusDate(new \DateTime());
                } else if ($non_available_total > 0 && $available_total > 0){
                    $status = generalReservation::STATUS_PARTIAL_AVAILABLE;
                    $reservation->setGenResStatus(generalReservation::STATUS_PARTIAL_AVAILABLE);
                    $reservation->setCurrentResponseTime();
                    $reservation->setGenResStatusDate(new \DateTime());
                }
                else if ($cancelled_total > 0 && $cancelled_total != $details_total) {
                    $status = generalReservation::STATUS_PARTIAL_CANCELLED;
                    $reservation->setGenResStatus(generalReservation::STATUS_PARTIAL_CANCELLED);
                    $reservation->setGenResStatusDate(new \DateTime());
                } else if ($outdated_total > 0 && $outdated_total == $details_total){
                    $status = generalReservation::STATUS_OUTDATED;
                    $reservation->setGenResStatus(generalReservation::STATUS_OUTDATED);
                    $reservation->setGenResStatusDate(new \DateTime());
                }

            }
            if ($cancelled_total > 0 && $cancelled_total == $details_total) {
                $status = generalReservation::STATUS_CANCELLED;
                $reservation->setGenResStatus(generalReservation::STATUS_CANCELLED);
                $reservation->setGenResStatusDate(new \DateTime());
            }

            if ($send_notification){
                if ($status == generalReservation::STATUS_AVAILABLE)
                    $status_url = $this->generateUrl('frontend_mycasatrip_available');
                else
                    $status_url = $this->generateUrl('frontend_mycasatrip_pending');

                $ownership = $reservation->getGenResOwnId();
                $own_name = Utils::urlNormalize($ownership->getOwnName());
                $own_url = $this->generateUrl('frontend_details_ownership', array('own_name' => $own_name));
                $rating = $ownership->getOwnRating();
                $photo = $ownership->getPhotos();
                if (count($photo) > 0){
                    $file_name = $photo[0]->getOwnPhoPhoto()->getPhoName();
                }else{
                    $file_name = "no_photo.png";
                }
                $url_images = $this->container->get('templating.helper.assets')->getUrl("uploads/ownershipImages/").$file_name;

                if ($reservation->getGenResUserId()->getUserLanguage()){
                    $lang = $reservation->getGenResUserId()->getUserLanguage()->getLangCode();
                }else{
                    $lang = "en";
                }


                $from = $this->get('translator')->trans(
                    'FROM_DATE',
                    array(),
                    'messages',
                    $lang
                );
                $to = $this->get('translator')->trans(
                    'TO_DATE',
                    array(),
                    'messages',
                    $lang
                );
                $status = $this->get('translator')->trans(
                    'AVAILABLE',
                    array(),
                    'messages',
                    $lang
                );
                $title_status = $this->get('translator')->trans(
                    'STATEMENT',
                    array(),
                    'messages',
                    $lang
                );


                $metadata = array(
                    "msg" => $ownership->getOwnName(),
                    "status" => $title_status.': <b>'.$status.'</b>',//generalReservation::getStatusName($status),
                    "codigo" => $reservation->getCASId(),
                    "from_to_date" => $from.": ".date_format($reservation->getGenResFromDate(),"d-m-Y")." ".$to.": ".date_format($reservation->getGenResToDate(),"d-m-Y"),
                    "url_status" => $status_url,
                    "own_url" => $own_url,
                    "rating" => $rating,
                    "url_images" => $url_images
                );
                /*$hash_email = hash('sha256', $reservation->getGenResUserId()->getUserEmail());
                $param = array(
                    'to' => [$hash_email."_mycp"],
                    'pending' => 0,
                    "metadata" => $metadata
                );
                $url = $this->container->getParameter('url.mean')."api/notifications/";
                Notifications::sendNotifications($url, $param, $this->getRequest()->getSession()->get('access-token'));*/
            }

            $this->em->persist($reservation);
            $this->em->flush();
            $this->logger->saveLog('Edit entity for ' . $reservation->getCASId(), BackendModuleName::MODULE_RESERVATION);

        }
        return $errors;
        
    }


    private function processPost($post)
    {
        $errors = array();
        $details_total = 0;
        $available_total = 0;
        $non_available_total = 0;
        $cancelled_total = 0;
        $outdated_total = 0;

        $keys = array_keys($post);

        foreach ($keys as $key) {
            $splitted = explode("_", $key);
            $own_res_id = $splitted[count($splitted) - 1];
            if (strpos($key, 'service_room_price') !== false) {

                if (!is_numeric($post[$key])) {
                    $errors[$key] = 1;
                }
            }
            if (strpos($key, 'service_own_res_status') !== false) {
                $details_total++;
                switch ($post[$key]) {
                    case ownershipReservation::STATUS_NOT_AVAILABLE: $non_available_total++;
                        break;
                    case ownershipReservation::STATUS_AVAILABLE: $available_total++;
                        break;
                    case ownershipReservation::STATUS_CANCELLED: $cancelled_total++;
                        break;
                    case ownershipReservation::STATUS_OUTDATED: $outdated_total++;
                        break;
                }
            }

            if (strpos($key, 'date_from') !== false) {
                $start = explode('/', $post['date_from_' . $own_res_id]);
                $end = explode('/', $post['date_to_' . $own_res_id]);
                $start_timestamp = mktime(0, 0, 0, $start[1], $start[0], $start[2]);
                $end_timestamp = mktime(0, 0, 0, $end[1], $end[0], $end[2]);

                if ($start_timestamp > $end_timestamp) {
                    $errors[$key] = 1;
                    $errors["date_from"] = 1;
                }
            }

            if(strpos($key, 'service_room_count_adults') !== false)
            {
                $adults =  $post['service_room_count_adults_' . $own_res_id];
                $children =  $post['service_room_count_childrens_' . $own_res_id];

                if($adults + $children == 0)
                {
                    $errors["guest_number_" . $own_res_id] = 1;
                    $errors["guest_number"] = 1;
                }
            }
        }

        return array(
            "errors" => $errors,
            "detailsTotal" => $details_total,
            "available" => $available_total,
            "nonAvailable" => $non_available_total,
            "cancelled" => $cancelled_total,
            "outdated" => $outdated_total
        );
    }

    private function updateICal($roomId) {
        try {
            $room = $this->em->getRepository("mycpBundle:room")->find($roomId);
            $this->calendarService->createICalForRoom($room->getRoomId(), $room->getRoomCode());
            return "Se actualizó satisfactoriamente el fichero .ics asociado a esta habitación.";
        } catch (\Exception $e) {
            var_dump( "Ha ocurrido un error mientras se actualizaba el fichero .ics de la habitación. Error: " . $e->getMessage());
            exit;
        }
    }

    private function getLoggedUserId(){
        $user = $this->container->get('security.context')->getToken()->getUser();

        return ($user != null) ? $user->getUserId(): null;
    }
}
