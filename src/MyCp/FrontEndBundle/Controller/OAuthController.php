<?php
/**
 * Created by PhpStorm.
 * User: Karel
 * Date: 11/5/14
 * Time: 12:18 PM
 */

namespace MyCp\FrontEndBundle\Controller;

use MyCp\FrontEndBundle\Form\FacebookLoginType;
use MyCp\FrontEndBundle\Form\Model\FacebookLogin;
use MyCp\FrontEndBundle\Helpers\Utils;
use MyCp\mycpBundle\Entity\currency;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\userTourist;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;

class OAuthController extends Controller
{
    public function facebookLoginAction(Request $request)
    {
        if ($request->getMethod() === "GET") {
            $fbLoginForm = $this->createForm(
                new FacebookLoginType(),
                new FacebookLogin(),
                array('action' => $this->generateUrl('facebook_login'))
            );

            return $this->render(
                'FrontEndBundle:oauth:facebookLogin.html.twig',
                array('fbLoginForm' => $fbLoginForm->createView())
            );
        } else {
            if ($request->getMethod() === "POST") {

                $form = $this->createForm(new FacebookLoginType(), new FacebookLogin());
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $userRepository = $em->getRepository("mycpBundle:user");

                    $fbLoginData = $form->getData();

                    //Validar el correo
                    if(Utils::validateEmail($fbLoginData->getEmail())) {

                        $user = $userRepository->findOneBy(array('user_email' => $fbLoginData->getEmail()));

                        if ($user == null) {

                            $user = new user();
                            $role = $em->getRepository('mycpBundle:role')->findBy(array('role_name' => 'ROLE_CLIENT_TOURIST'));

                            //If first-time-user using facebook, we should add him to db
                            $user->setUserName($fbLoginData->getEmail())
                                ->setUserEmail(strtolower($fbLoginData->getEmail()))
                                ->setUserUserName($fbLoginData->getName())
                                ->setUserLastName($fbLoginData->getLastName())
                                ->setUserRole("ROLE_CLIENT_TOURIST")
                                ->setUserAddress('')
                                ->setUserCity('')
                                ->setUserPhone('')
                                ->setUserPassword("")
                                ->setUserEnabled(true)//enable directly because this is a confirmed user email from facebook.
                                ->setUserCreatedByMigration(false)
                                ->setUserSubrole($role[0]);

                            $userTourist = new userTourist();

                            //default currency for the user
                            $currency = $em->getRepository('mycpBundle:currency')->findOneBy(array('curr_default' => true));

                            //default language for the user
//                        $languageCode = $request->attributes->get('app_lang_code');
//                        $languageCode = empty($languageCode) ? $request->attributes->get('_locale') : $request->getSession()->get('_locale', 'en');
                            $languageCode = $fbLoginData->getLanguage() ? $fbLoginData->getLanguage() : $request->attributes->get('app_lang_code');
                            $languageCode = strtoupper($languageCode);
                            $language = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $languageCode));

                            if ($language === null || !isset($language) || $language === "") {
                                $defaultLanguageCode = $this->container->getParameter("configuration.default.language.code");
                                $language = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $defaultLanguageCode));
                            }

                            //we can get the gender of the user from $fbLoginData->getGender() as male or female
                            $gender = $fbLoginData->getGender() == "male" ? 0 : 1;

                            //default country for the user
                            //$country = $em->getRepository('mycpBundle:country')->find('USA');
                            $user->setUserCountry($fbLoginData->getCountry());
                            $userTourist->setUserTouristCurrency($currency);
                            $userTourist->setUserTouristLanguage($language);
                            $userTourist->setUserTouristUser($user);
                            $userTourist->setUserTouristGender($gender);

                            $em->persist($user);
                            $em->persist($userTourist);


                            $hash_user = hash('sha256', $fbLoginData->getName());
                            $hash_email = hash('sha256', strtolower($fbLoginData->getEmail()));
                            $password="";
                            //Registrando al user en HDS-MEAN
                            // abrimos la sesión cURL
                            $ch = curl_init();
                            // definimos la URL a la que hacemos la petición
                            curl_setopt($ch, CURLOPT_URL,$this->container->getParameter('url.mean')."register");
                            // definimos el número de campos o parámetros que enviamos mediante POST
                            curl_setopt($ch, CURLOPT_POST, 1);
                            // definimos cada uno de los parámetros
                            curl_setopt($ch, CURLOPT_POSTFIELDS, "email=".$hash_email.'_'.$this->container->getParameter('mean_project')."&last=".$fbLoginData->getLastName()."&first=".$fbLoginData->getLastName()."&password=".$password."&username=".$hash_user.'_'.$this->container->getParameter('mean_project'));
                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            // recibimos la respuesta y la guardamos en una variable
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $remote_server_output = curl_exec ($ch);
                            // cerramos la sesión cURL
                            curl_close ($ch);
                            $user->setRegisterNotification(true);
                            $em->persist($user);
                            $em->flush();
                        }

                        //authenticate the user
                        $providerKey = 'user'; //the name of the firewall
                        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
                        $this->get("security.context")->setToken($token);
                        $this->get('session')->set('_security_user', serialize($token));
                        $this->afterLogin();
                        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
                        $cartItems = $em->getRepository('mycpBundle:cart')->getCartItemsAfterLoginFacebook($user_ids);

                        $cartItemsQueryBooking = $em->getRepository('mycpBundle:cart')->getQueryBookingAfterLoginFacebook($user_ids);

                        if(count($cartItems)){
                            $ownerShip=$em->getRepository('mycpBundle:generalReservation')->getOwnShipReserByUser($user_ids);
                            $insert=1;
                            //Validar que no se haga una reserva que ya fuese realizada
                            foreach ($ownerShip as $item){
                                $ownDateFrom = $item->getOwnResReservationFromDate()->getTimestamp();
                                $ownDateTo = $item->getOwnResReservationToDate()->getTimestamp();


                                foreach ($cartItems as $cart) {
                                    $cartDateFrom = $cart->getCartDateFrom()->getTimestamp();
                                    $cartDateTo = $cart->getCartDateTo()->getTimestamp();
                                    if((($ownDateFrom <= $cartDateFrom && $ownDateTo >= $cartDateFrom) ||
                                            ($ownDateFrom <= $cartDateTo && $ownDateTo >= $cartDateTo))
                                        && $item->getOwnResSelectedRoomId()==$cart->getCartRoom()->getRoomId())
                                        $insert=0;
                                }
                            }
                            if($insert==1){  //sino hay un error
                                $arrayIdCart=array();
                                foreach ($cartItems as $cart){
                                    $arrayIdCart[]=$cart->getCartId();
                                }
                                $own_ids=array();
                                //Es que el usuario mando a hacer una reserva
                                $own_ids=$this->checkDispo($arrayIdCart,$request,true);
                                $request->getSession()->set('reservation_own_ids', $own_ids);
                                return $this->redirect($this->generateUrl('frontend_reservation_reservation'));
                            }
                            else{
                                $message = $this->get('translator')->trans("ADD_TO_CEST_ERROR");
                                $this->get('session')->getFlashBag()->add('message_global_error', $message);
                                return $this->redirect($this->generateUrl('frontend_mycasatrip_available'));
                            }
                        }
                        if(count($cartItemsQueryBooking)){

                            $ownerShip=$em->getRepository('mycpBundle:generalReservation')->getOwnShipReserByUser($user_ids);
                            $insert=1;
                            //Validar que no se haga una reserva que ya fuese realizada
                            foreach ($ownerShip as $item){
                                $ownDateFrom = $item->getOwnResReservationFromDate()->getTimestamp();
                                $ownDateTo = $item->getOwnResReservationToDate()->getTimestamp();
                                foreach ($cartItems as $cart) {
                                    $cartDateFrom = $cart->getCartDateFrom()->getTimestamp();
                                    $cartDateTo = $cart->getCartDateTo()->getTimestamp();
                                    if((($ownDateFrom <= $cartDateFrom && $ownDateTo >= $cartDateFrom) ||
                                            ($ownDateFrom <= $cartDateTo && $ownDateTo >= $cartDateTo))
                                        && $item->getOwnResSelectedRoomId()==$cart->getCartRoom()->getRoomId())
                                        $insert=0;
                                }
                            }
                            if($insert==1){  //sino hay un error
                                $arrayIdCart=array();
                                foreach ($cartItemsQueryBooking as $cart){
                                    $arrayIdCart[]=$cart->getCartId();
                                }
                                $own_ids=array();
                                //Es que el usuario mando a hacer una consulta de disponibilidad
                                $own_ids=$this->checkDispo($arrayIdCart,$request,false);
                                $request->getSession()->set('reservation_own_ids', $own_ids);
                                return $this->redirect($this->generateUrl('frontend_mycasatrip_pending'));
                            }
                            else{
                                $message = $this->get('translator')->trans("ADD_TO_CEST_ERROR");
                                $this->get('session')->getFlashBag()->add('message_global_error', $message);
                                return $this->redirect($this->generateUrl('frontend_mycasatrip_pending'));
                            }
                        }

                        $hash_user = hash('sha256', $user->getUserUserName());
                        $hash_email = hash('sha256', $user->getUserEmail());
                        //-----------------Autenticando al usuario en HDS-MEN
                        $session = $this->container->get('session');
                        //// abrimos la sesión cURL
                        $ch = curl_init();
                        // definimos la URL a la que hacemos la petición
                        curl_setopt($ch, CURLOPT_URL,$this->container->getParameter('url.mean')."access-token?username=".$hash_user.'_'.$this->container->getParameter('mean_project')."&password=".$user->getPassword()."&email=".$hash_email.'_'.$this->container->getParameter('mean_project'));
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        // recibimos la respuesta y la guardamos en una variable
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec ($ch);
                        // cerramos la sesión cURL
                        curl_close ($ch);
                        if(!$response) {
                            $session->set('access-token', "");
                        }else{
                            $response_temp= json_decode($response);
                            $session->set('access-token', $response_temp->token);
                            $user->setOnline(true);
                            $em->persist($user);
                            $em->flush();
                        }

                    }
                    else{
                        //Mensaje de error
                        $message = $this->get('translator')->trans("EMAIL_INVALID_MESSAGE");
                        $this->get('session')->getFlashBag()->add('message_global_error', $message);
                    }
                }
            }
        }
        return $this->redirect($this->generateUrl("frontend_welcome"));
    }
    /**
     * @param $id_car
     * @param $request
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function checkDispo($arrayIdCart,$request,$inmediatily_booking){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $reservations = array();
        $own_ids = array();
        $array_photos = array();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        foreach($arrayIdCart as $temp){
            $cartItem = $em->getRepository('mycpBundle:cart')->find($temp);
            $cartItems[]=$cartItem;
        }

        $min_date = null;
        $max_date = null;
        $generalReservations = array();

        if (count($cartItems) > 0) {
            $res_array = array();
            $own_visited = array();
            foreach ($cartItems as $item) {

                if ($min_date == null)
                    $min_date = $item->getCartDateFrom();
                else if ($item->getCartDateFrom() < $min_date)
                    $min_date = $item->getCartDateFrom();

                if ($max_date == null)
                    $max_date = $item->getCartDateTo();
                else if ($item->getCartDateTo() > $max_date)
                    $max_date = $item->getCartDateTo();

                $res_own_id = $item->getCartRoom()->getRoomOwnership()->getOwnId();

                $array_group_by_own_id = array();
                $flag = 1;
                foreach ($own_visited as $own) {
                    if ($own == $res_own_id) {
                        $flag = 0;
                    }
                }
                if ($flag == 1)
                    foreach ($cartItems as $item) {
                        if ($res_own_id == $item->getCartRoom()->getRoomOwnership()->getOwnId()) {
                            array_push($array_group_by_own_id, $item);
                        }
                    }
                array_push($res_array, $array_group_by_own_id);
                array_push($own_visited, $res_own_id);
            }
            $service_time = $this->get('Time');
            $nigths = array();
            foreach ($res_array as $resByOwn) {
                if (isset($resByOwn[0])) {
                    $ownership = $em->getRepository('mycpBundle:ownership')->find($resByOwn[0]->getCartRoom()->getRoomOwnership()->getOwnId());

                    $serviceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
                    $general_reservation = new generalReservation();
                    $general_reservation->setGenResUserId($user);
                    $general_reservation->setGenResDate(new \DateTime(date('Y-m-d')));
                    $general_reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
                    $general_reservation->setGenResHour(date('G'));
                    if($inmediatily_booking)
                        $general_reservation->setGenResStatus(generalReservation::STATUS_AVAILABLE);
                    else
                        $general_reservation->setGenResStatus(generalReservation::STATUS_PENDING);
                    $general_reservation->setGenResFromDate($min_date);
                    $general_reservation->setGenResToDate($max_date);
                    $general_reservation->setGenResSaved(0);
                    $general_reservation->setGenResOwnId($ownership);
                    $general_reservation->setGenResDateHour(new \DateTime(date('H:i:s')));
                    $general_reservation->setServiceFee($serviceFee);


                    $total_price = 0;
                    $partial_total_price = array();
                    $destination_id = ($ownership->getOwnDestination() != null) ? $ownership->getOwnDestination()->getDesId() : null;
                    foreach ($resByOwn as $item) {
                        $triple_room_recharge = ($item->getTripleRoomCharged()) ? $this->container->getParameter('configuration.triple.room.charge') : 0;
                        $array_dates = $service_time->datesBetween($item->getCartDateFrom()->getTimestamp(), $item->getCartDateTo()->getTimestamp());
                        $temp_price = 0;
                        $seasons = $em->getRepository("mycpBundle:season")->getSeasons($item->getCartDateFrom()->getTimestamp(), $item->getCartDateTo()->getTimestamp(), $destination_id);

                        for ($a = 0; $a < count($array_dates) - 1; $a++) {
                            $seasonType = $service_time->seasonTypeByDate($seasons, $array_dates[$a]);
                            $roomPrice = $item->getCartRoom()->getPriceBySeasonType($seasonType);
                            $total_price += $roomPrice + $triple_room_recharge;
                            $temp_price += $roomPrice + $triple_room_recharge;
                        }
                        array_push($partial_total_price, $temp_price);
                    }
                    $general_reservation->setGenResTotalInSite($total_price);
                    $em->persist($general_reservation);

                    $arrayKidsAge = array();

                    $flag_1 = 0;
                    foreach ($resByOwn as $item) {
                        $ownership_reservation = $item->createReservation($general_reservation, $partial_total_price[$flag_1]);
                        if($inmediatily_booking)
                            $ownership_reservation->setOwnResStatus(ownershipReservation::STATUS_AVAILABLE);


                        array_push($reservations, $ownership_reservation);

                        $ownership_photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($ownership_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId());
                        array_push($array_photos, $ownership_photo);

                        $nightsCount = $service_time->nights($ownership_reservation->getOwnResReservationFromDate()->getTimestamp(), $ownership_reservation->getOwnResReservationToDate()->getTimestamp());
                        array_push($nigths, $nightsCount);

                        if($item->getChildrenAges() != null)
                        {
                            $arrayKidsAge[$item->getCartRoom()->getRoomNum()] = $item->getChildrenAges();
                        }

                        $em->persist($ownership_reservation);
                        $em->flush();
                        array_push($own_ids, $ownership_reservation->getOwnResId());
                        $flag_1++;
                    }
                    $general_reservation->setChildrenAges($arrayKidsAge);
                    $em->flush();

                    //update generalReservation dates
                    $em->getRepository("mycpBundle:generalReservation")->updateDates($general_reservation);
                    array_push($generalReservations, $general_reservation->getGenResId());

                    if($general_reservation->getGenResOwnId()->getOwnInmediateBooking()){
                        $smsService = $this->get("mycp.notification.service");
                        $smsService->sendInmediateBookingSMSNotification($general_reservation);
                    }

                }
            }
        } else {
            return false;
        }
        $locale = $this->get('translator')->getLocale();
        // Enviando mail al cliente
        if(!$inmediatily_booking){
            $body = $this->render('FrontEndBundle:mails:email_check_available.html.twig', array(
                    'user' => $user,
                    'reservations' => $reservations,
                    'ids' => $own_ids,
                    'nigths' => $nigths,
                    'photos' => $array_photos,
                    'user_locale' => $locale
                ));

            if($user != null) {
                $locale = $this->get('translator');
                $subject = $locale->trans('REQUEST_SENT');
                $service_email = $this->get('Email');
                $service_email->sendEmail(
                    $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com', $user->getUserEmail(), $body
                );
            }
        }

        if(!$inmediatily_booking){
            //Enviando mail al reservation team
            foreach($generalReservations as $genResId){
                //Enviando correo a solicitud@mycasaparticular.com
                \MyCp\FrontEndBundle\Helpers\ReservationHelper::sendingEmailToReservationTeam($genResId, $em, $this, $service_email, $service_time, $request, 'solicitud@mycasaparticular.com', 'no-reply@mycasaparticular.com');
            }
        }
        foreach($arrayIdCart as $temp){
            $cartItem = $em->getRepository('mycpBundle:cart')->find($temp);
            $em->remove($cartItem);
        }
        $em->flush();
        if(!$inmediatily_booking) //esta consultando la disponibilidad
            return true;
        else                      //esta haciendo una reserva
            return $own_ids;
    }

    /**
     * Función para despues que se autentique en facebook
     */
    public function afterLogin(){
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $session = $this->container->get('session');

        //Pasar lo q esta en los favoritos al usuario loggueado
        $session_id = $em->getRepository('mycpBundle:user')->getSessionIdWithRequest($this->container->get('request'));

        if ($session_id != null)
        {
            $em->getRepository('mycpBundle:favorite')->setToUser($user, $session_id);
            $em->getRepository('mycpBundle:userHistory')->setToUser($user, $session_id);
            $em->getRepository('mycpBundle:cart')->setToUser($user, $session_id);
        }

        //Cambiar el sitio a la moneda y lenguaje ultimo del sitio almacenados en userTourist
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));

        $tourist_currency = null;
        if ($userTourist) {
            $tourist_currency = $userTourist->getUserTouristCurrency();

            if (!$tourist_currency) {
                $curr_by_default = $em->getRepository('mycpBundle:currency')->findOneBy(array('curr_default' => 1));

                if (isset($curr_by_default)) {
                    $this->container->get('session')->set("curr_rate", $curr_by_default->getCurrCucChange());
                    $this->container->get('session')->set("curr_symbol", $curr_by_default->getCurrSymbol());
                    $this->container->get('session')->set("curr_acronym", $curr_by_default->getCurrCode());
                } else {
                    $price_in_currency = $this->em->getRepository('mycpBundle:currency')->findOneBy(array('curr_site_price_in' => true));
                    $session->set("curr_rate", $price_in_currency->getCurrCucChange());
                    $session->set("curr_symbol", $price_in_currency->getCurrSymbol());
                    $session->set("curr_acronym", $price_in_currency->getCurrCode());
                }
            }

            $tourist_language = $userTourist->getUserTouristLanguage();

            if (isset($tourist_language)) {
                $locale = strtolower($tourist_language->getLangCode());
                $session->set('user_lang', $locale);
                $session->set('app_lang_name', $tourist_language->getLangName());
                $session->set('app_lang_code', $tourist_language->getLangCode());
                $session->set("just_logged", true);
            }
        }
    }
    public function checkEmailAction(Request $request){
        $email=$request->get('email');
        if($email != "" && Utils::validateEmail($email)) {
            $em=$this->getDoctrine()->getManager();
            $userRepository = $em->getRepository("mycpBundle:user");
            $user = $userRepository->findOneBy(array('user_email' => $email));
            $response=array();
            $response['exists']=($user!=null)?true:false;
        }
        $response['exists']=false;
        return new JsonResponse($response);
    }
}
