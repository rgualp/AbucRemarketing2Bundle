<?php

namespace MyCp\FrontEndBundle\Controller;

use Abuc\RemarketingBundle\Event\JobEvent;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use MyCp\FrontEndBundle\Helpers\SkrillHelper;
use MyCp\FrontEndBundle\Helpers\PostFinanceHelper;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\payment;
use MyCp\mycpBundle\Entity\postfinancePayment;
use MyCp\mycpBundle\Entity\skrillPayment;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\userTourist;
use MyCp\mycpBundle\JobData\PaymentJobData;
use Omnipay\Omnipay;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class PaymentController extends Controller
{

    private static $skrillPostUrl = 'https://www.moneybookers.com/app/payment.pl';
    private static $postFinance = 'https://e-payment.postfinance.ch/ncol/prod/orderstandard.asp';

    const MAX_SKRILL_NUM_DETAILS = 5;
    const MAX_SKRILL_DETAIL_STRING_LENGTH = 240;

    /**
     * Action method to initiate a Payment with Skrill.
     *
     * @param int $bookingId The id of the booking the user wants to pay for
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function skrillPaymentAction($bookingId)
    {
        $booking = $this->getBookingFrom($bookingId);

        if (empty($booking)) {
            throw new EntityNotFoundException($bookingId);
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('mycpBundle:user')->find($booking->getBookingUserId());

        if (empty($user)) {
            throw new EntityNotFoundException("user($user)");
        }

        $loggedInUser = $this->getUser();

        if (empty($loggedInUser)) {
            throw new AuthenticationException('User not logged in.');
        }

        if ($user->getUserId() !== $loggedInUser->getUserId()) {
            throw new AuthenticationException('Access to resource not permitted.');
        }

        $userTourist = $em
            ->getRepository('mycpBundle:userTourist')
            ->findOneBy(array('user_tourist_user' => $user->getUserId()));

        if (empty($userTourist)) {
            throw new EntityNotFoundException("userTourist($userTourist)");
        }

        $skrillData = $this->getSkrillViewData($booking, $user, $userTourist);

        return $this->render('FrontEndBundle:payment:skrillPayment.html.twig', $skrillData);
    }


    public function postFinancePaymentAction($bookingId, $method = "VISA")
    {
        $booking = $this->getBookingFrom($bookingId);

        if (empty($booking)) {
            throw new EntityNotFoundException($bookingId);
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('mycpBundle:user')->find($booking->getBookingUserId());

        if (empty($user)) {
            throw new EntityNotFoundException("user($user)");
        }

        $loggedInUser = $this->getUser();

        if (empty($loggedInUser)) {
            throw new AuthenticationException('User not logged in.');
        }

        if ($user->getUserId() !== $loggedInUser->getUserId()) {
            throw new AuthenticationException('Access to resource not permitted.');
        }

        $userTourist = $em
            ->getRepository('mycpBundle:userTourist')
            ->findOneBy(array('user_tourist_user' => $user->getUserId()));

        if (empty($userTourist)) {
            throw new EntityNotFoundException("userTourist($userTourist)");
        }

        $postFinanceData = $this->getPostFinanceViewData($booking, $user, $userTourist, $method);

        return $this->render('FrontEndBundle:payment:postPayment.html.twig', $postFinanceData);
    }


    /**
     * Action method the user should be directed to when returning from
     * the Skrill payment website.
     *
     * @param $bookingId
     * @return Response
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function skrillReturnAction($bookingId)
    {
        $booking = $this->getBookingFrom($bookingId);

        if (empty($booking)) {
            throw new InvalidParameterException($bookingId);
        }

        $pollingUrl = $this->generateUrl('frontend_payment_poll_payment',
            array('bookingId' => $bookingId), UrlGeneratorInterface::ABSOLUTE_URL);

        $confirmationUrl = $this->generateUrl('frontend_confirmation_reservation',
            array('id_booking' => $bookingId), UrlGeneratorInterface::ABSOLUTE_URL);

        $pendingUrl = $this->generateUrl('frontend_confirmation_reservation',
            array('id_booking' => $bookingId), UrlGeneratorInterface::ABSOLUTE_URL);

        $timeoutUrl = $this->generateUrl('frontend_mycasatrip_available',
            array(), UrlGeneratorInterface::ABSOLUTE_URL);

        $cancelUrl = $this->generateUrl('frontend_mycasatrip_available',
            array(), UrlGeneratorInterface::ABSOLUTE_URL);

        $failedUrl = $this->generateUrl('frontend_mycasatrip_available',
            array(), UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render(
            'FrontEndBundle:payment:waitingForPayment.html.twig',
            array(
                'pollingUrl' => $pollingUrl,
                'confirmationUrl' => $confirmationUrl,
                'timeoutUrl' => $timeoutUrl,
                'cancelUrl' => $cancelUrl,
                'pendingUrl' => $pendingUrl,
                'failedUrl' => $failedUrl,
                'timeoutTicks' => 80,
                'pollingPeriodMsec' => 1000
            )
        );
    }

    /**
     * Action method that gets called from JavaScript in waitingForPayment.html.twig
     * to receive the current payment status in JSON format.
     *
     * @param $bookingId
     * @return JsonResponse
     */
    public function pollPaymentAction($bookingId)
    {
        $booking = $this->getBookingFrom($bookingId);

        if (empty($booking)) {
            return new JsonResponse(array('status' => 'booking_not_found'));
        }

        $payment = $this->getPaymentFrom($booking);

        if (empty($payment)) {
            return new JsonResponse(array('status' => 'payment_not_found'));
        }

        switch ($payment->getStatus()) {
            case PaymentHelper::STATUS_PROCESSED:
            case PaymentHelper::STATUS_SUCCESS:
                return new JsonResponse(array('status' => 'payment_found'));
            case PaymentHelper::STATUS_FAILED:
                return new JsonResponse(array('status' => 'payment_failed'));
            case PaymentHelper::STATUS_CANCELLED:
                return new JsonResponse(array('status' => 'payment_cancelled'));
            default:
                return new JsonResponse(array('status' => 'payment_pending'));
        }
    }

    /**
     * Action method called by Skrill with http POST data which
     * contains all information about a payment a user made.
     *
     * The information is stored in the database (skrillPayment and payment tables)
     * and a http Response (status 200 for success, 400 for failure) is returned as an answer.
     *
     * @return Response
     */
    public function skrillStatusAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest()->request->all();

        $this->log(date('Y-m-d H:i:s') . ': PaymentController line ' . __LINE__ . ': Received Skrill status.');

        if (empty($request)) {
            return new Response('Empty post data', 400);
        }

        $this->log('PaymentController line ' . __LINE__ . "Request: \n" . print_r($request, true));

        list($skrillPayment, $payment) = $this->updatePaymentData($em, $request);

        $bookingService = $this->get('front_end.services.booking');
        $bookingService->postProcessBookingPayment($payment);

        /*$dispatcher = $this->get('event_dispatcher');
        $eventData = new PaymentJobData($payment->getId());
        $dispatcher->dispatch('mycp.event.postpayment', new JobEvent($eventData));*/

        $this->log(date(DATE_RSS) . ' - PaymentController line ' . __LINE__ .
            ': Payment ID: ' . $payment->getId() . "\nSkrillRequest ID: " . $skrillPayment->getId());

        return new Response('Thanks', 200);
    }

    /**
     * Action method called by Postfinance with http POST data which
     * contains all information about a payment a user made.
     *
     * The information is stored in the database (postfinancePayment and payment tables)
     * and a http Response (status 200 for success, 400 for failure) is returned as an answer.
     *
     * @return Response
     */
    public function postfinanceStatusAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest()->request->all();

        $queryString = $this->getRequest()->getQueryString();
        $request = $this->getArrayFromRequest($queryString);

        $this->log(date('Y-m-d H:i:s') . ': PaymentController line ' . __LINE__ . ': Received Postfinance status.');

        if (empty($request)) {
            return new Response('Empty post data', 400);
        }

        $this->log('PaymentController line ' . __LINE__ . "Request: \n" . print_r($request, true));

        list($postfinancePayment, $payment) = $this->updatePostfinancePaymentData($em, $request);

        $bookingService = $this->get('front_end.services.booking');
        $bookingService->postProcessBookingPayment($payment);

        /*$dispatcher = $this->get('event_dispatcher');
        $eventData = new PaymentJobData($payment->getId());
        $dispatcher->dispatch('mycp.event.postpayment', new JobEvent($eventData));*/

        $this->log(date(DATE_RSS) . ' - PaymentController line ' . __LINE__ .
            ': Payment ID: ' . $payment->getId() . "\nPostfinanceRequest ID: " . $postfinancePayment->getId());

        //return new Response('Thanks', 200);
        $trans = $this->get('translator');
        $message = $trans->trans('PAYMENT_SUCCESS');
        $this->get('session')->getFlashBag()->add('message_global_success', $message);
        $payment->generateEcomerceTracking($this->getDoctrine()->getManager(), $this);
        return $this->redirect($this->generateUrl("frontend_mycasatrip_payment"));
    }

    private function getArrayFromRequest($queryString){
        $arrayFromQuestyString = explode("&", $queryString);
        $result = array();

        foreach($arrayFromQuestyString as $pair)
        {
            $pair = explode("=", $pair);
            $key = $pair[0];
            $value = $pair[1];

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Updates or creates the payment and skrillPayment data according
     * to the POST request received from Skrill.
     *
     * @param $em
     * @param $request
     * @return array
     * @throws \LogicException
     */
    private function updatePaymentData($em, $request)
    {
        $skrillPayment = new skrillPayment($request);
        $bookingId = $skrillPayment->getMerchantTransactionId();
        $this->log('PaymentController line ' . __LINE__ . ': Booking id=' . $bookingId . '');
        $booking = $this->getBookingFrom($bookingId);

        if (empty($booking)) {
            $this->log('PaymentController line ' . __LINE__ . ': Booking (id=' . $bookingId . ') not found.');
            return new Response('', 200);
        }

        $payment = $this->getPaymentFrom($booking);

        $this->log('PaymentController line ' . __LINE__ . "Payment found: " . empty($payment) ? 'yes' : 'no');

        if (empty($payment)) {
            $payment = new payment();
            $payment->setCreated(new DateTime());
            $payment->setBooking($booking);
        }

        $this->log('SkrillPayment:');
        $this->log('merchant amount: ' . $skrillPayment->getMerchantAmount());
        $this->log('merchant currency: ' . $skrillPayment->getMerchantCurrency());
        $this->log('status: ' . $skrillPayment->getStatus());
        $this->log('merchant transaction id (booking_id): ' . $skrillPayment->getMerchantTransactionId());

        $payment->setPayedAmount($skrillPayment->getMerchantAmount());

        $currencyIsoCode = $skrillPayment->getMerchantCurrency();
        $currency = $this->getCurrencyFrom($currencyIsoCode);

        if (empty($currency)) {
            $this->log(date(DATE_RSS) . ' - PaymentController line ' . __LINE__ .
                ': Currency ' . $currencyIsoCode . ' not found.');
            throw new \LogicException('Currency not found: ' . $currencyIsoCode);
        }

        $payment->setCurrency($currency);
        $payment->setModified(new DateTime());
        $payment->setStatus(SkrillHelper::getInternalStatusCodeFrom($skrillPayment->getStatus()));
        $currencyRate = $currency->getCurrCucChange();
        $payment->setCurrentCucChangeRate($currencyRate);

        $skrillPayment->setPayment($payment);
        $em->persist($payment);
        $em->persist($skrillPayment);
        $em->flush();

        return array($skrillPayment, $payment);
    }

    /**
     * Updates or creates the payment and skrillPayment data according
     * to the POST request received from Skrill.
     *
     * @param $em
     * @param $request
     * @return array
     * @throws \LogicException
     */
    private function updatePostfinancePaymentData($em, $request)
    {
        $postfinancePayment = new postfinancePayment($request);
        $bookingId = $postfinancePayment->getOrderId();
        $this->log('PaymentController line ' . __LINE__ . ': Booking id=' . $bookingId . '');
        $booking = $this->getBookingFrom($bookingId);

        if (empty($booking)) {
            $this->log('PaymentController line ' . __LINE__ . ': Booking (id=' . $bookingId . ') not found.');
            return new Response('', 200);
        }

        $payment = $this->getPaymentFrom($booking);

        $this->log('PaymentController line ' . __LINE__ . "Payment found: " . empty($payment) ? 'yes' : 'no');

        if (empty($payment)) {
            $payment = new payment();
            $payment->setCreated(new DateTime());
            $payment->setBooking($booking);
        }

        $this->log('Postfinance:');
        $this->log('merchant amount: ' . $postfinancePayment->getAmount());
        $this->log('merchant currency: ' . $postfinancePayment->getCurrency());
        $this->log('status: ' . $postfinancePayment->getStatus());
        $this->log('order id (booking_id): ' . $postfinancePayment->getOrderId());

        $payment->setPayedAmount($postfinancePayment->getAmount());

        $currencyIsoCode = $postfinancePayment->getCurrency();
        $currency = $this->getCurrencyFrom($currencyIsoCode);

        if (empty($currency)) {
            $this->log(date(DATE_RSS) . ' - PaymentController line ' . __LINE__ .
                ': Currency ' . $currencyIsoCode . ' not found.');
            throw new \LogicException('Currency not found: ' . $currencyIsoCode);
        }

        $payment->setCurrency($currency);
        $payment->setModified(new DateTime());
        $payment->setStatus(PostFinanceHelper::getInternalStatusCodeFrom($postfinancePayment->getStatus()));
        $currencyRate = $currency->getCurrCucChange();
        $payment->setCurrentCucChangeRate($currencyRate);

        $postfinancePayment->setPayment($payment);
        $em->persist($payment);
        $em->persist($postfinancePayment);
        $em->flush();

        return array($postfinancePayment, $payment);
    }

    /**
     * Testing method that can be called when the user cancels a Skrill payment.
     *
     * Can be removed when a more appropriate method has been defined.
     *
     * @return Response
     */
    public function skrillCancelAction()
    {
        return $this->render(
            'FrontEndBundle:payment:skrillResponseTest.html.twig',
            array('status' => 'Cancelled by Skrill'));
    }

    public function postfinanceCancelAction()
    {
        return $this->render(
            'FrontEndBundle:payment:skrillResponseTest.html.twig',
            array('status' => 'Cancelled by Postfinance'));
    }

    /**
     * Testing method to test a Skrill status response.
     *
     * @param $status
     * @return Response
     */
    public function skrillTestResponseAction($status)
    {
        return $this->render(
            'FrontEndBundle:payment:skrillResponseTest.html.twig',
            array('status' => $status));
    }

    /**
     * With this method the skrill payment can be tested instead of using the real button
     * on the reservation page
     *
     * @param int $bookingId
     * @return Response
     */
    public function skrillTestPaymentAction($bookingId = 0)
    {
        $payUrl = $this->generateUrl('frontend_payment_skrill', array('bookingId' => $bookingId), true);
        return $this->render(
            'FrontEndBundle:payment:skrillPaymentTest.html.twig', array('payUrl' => $payUrl));
    }

    /**
     * This function emulates the POST status request from Skrill so a real payment needs
     * not to be done.
     *
     * @param $bookingId
     * @param $status
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function skrillSendTestPostRequestAction($bookingId, $status)
    {
        $urltopost = $this->generateUrl('frontend_payment_skrill_status', array(), true);

        $datatopost = array(
            'sha2sig' => '055C071F19CF8986C3EB682FFE8A5885511E0C6381E80D73B8A69E1830FA10C1',
            'status' => $status,
            'md5sig' => '3279807BFDBECC92796250FDD09D05FF',
            'merchant_id' => '22512989',
            'pay_to_email' => 'accounting@mycasaparticular.com',
            'mb_amount' => '12.50214',
            'mb_transaction_id' => '970004731',
            'currency' => 'EUR',
            'amount' => '10.22',
            'customer_id' => '33885759',
            'payment_type' => 'VSA',
            'transaction_id' => $bookingId,
            'pay_from_email' => 'ct@mycasaparticular.com',
            'mb_currency' => 'CHF',
        );

        $ch = curl_init($urltopost);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datatopost);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        $this->log(date(DATE_RSS) . ' - PaymentController line ' . __LINE__ . ', TEST POST REQUEST'.PHP_EOL.
            ': URL ' . $urltopost . '. booking id: ' . $bookingId . 'Result: ' . print_r($result, true));

        return $this->redirect($this->generateUrl(
            'frontend_payment_skrill_return', array('bookingId' => $bookingId)));
    }

    private function getBookingFrom($bookingId)
    {
        try {
            return $this->getDoctrine()
                ->getRepository('mycpBundle:booking')
                ->find($bookingId);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getPaymentFrom($booking)
    {
        try {
            return $this->getDoctrine()
                ->getRepository('mycpBundle:payment')
                ->findOneBy(array('booking' => $booking));
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getCurrencyFrom($currencyIsoCode)
    {
        $currencyIsoCode = strtoupper(trim($currencyIsoCode));

        try {
            return $this->getDoctrine()
                ->getRepository('mycpBundle:currency')
                ->findOneBy(array('curr_code' => $currencyIsoCode));
        } catch (\Exception $e) {
            return null;
        }
    }



    private function getPostFinanceViewData(booking $booking, user $user, userTourist $userTourist, $method = "POSTFINANCE")
    {
        $bookingId = $booking->getBookingId();
        $translator = $this->get('translator');
        $locale = $this->getRequest()->getLocale();
        $relativeLogoUrl = $this->container->get('templating.helper.assets')->getUrl('bundles/frontend/img/mycp.png');
        $logoUrl = $this->getRequest()->getSchemeAndHttpHost() . $relativeLogoUrl;
        $pspid = "abuc1";
        $amount = round($booking->getBookingPrepay(), 2);

        $gateway = Omnipay::create('Postfinance'); //Omnipay::create('Postfinance');
        $gateway->setPspId($pspid);
        $gateway->setShaIn('abcdefghi1234567');
        $gateway->setShaOut('abcdefghi1234567');
        $gateway->setLanguage(PostFinanceHelper::getPostFinanceLanguageFromLocale($locale));
        $gateway->setTestMode(false);
        $gateway->setLogo($logoUrl);

        // Send purchase request
        $response = $gateway->purchase(
            [
                'transactionId' => $bookingId,
                'amount' => $amount,
                'method' => $method,
                'currency' => $booking->getBookingCurrency()->getCurrCode(),
                'returnUrl' => $this->generateUrl('frontend_payment_postfinance_status', array(), true),
                'notifyUrl' => $this->generateUrl('frontend_payment_postfinance_cancel', array(), true),
                'cancelUrl' => $this->generateUrl('frontend_payment_postfinance_cancel', array(), true)
            ]
        )->send();

        // This is a redirect gateway, so redirect right away
        $response->redirect();

        /*$pspid = "abucTEST";
        $secretPassword = "3w2bWAgHt147852veiuno*";
        $amount = round($booking->getBookingPrepay(), 2) * 100;

        $sha1 = sha1($bookingId.$amount.$booking->getBookingCurrency()->getCurrCode().$pspid.$secretPassword);


//        $skrillData = array(
        $postFinanceData = array(
            'action_url' => self::$postFinance,
            'PSPID' => $pspid,
            'orderID' => $bookingId,//ORDER
            'amount' => $amount,
            'currency' => $booking->getBookingCurrency()->getCurrCode(),
            'language' => PostFinanceHelper::getPostFinanceLanguageFromLocale($locale),
            'ACCEPTURL' => $this->generateUrl('frontend_payment_postfinance_status', array(), true), //esta es la url que se llama si el pago fue exitoso
            'DECLINEURL' => $this->generateUrl('frontend_payment_skrill_cancel', array(), true),
            'EXCEPTIONURL' => $this->generateUrl('frontend_payment_skrill_cancel', array(), true),
            'CANCELURL' => $this->generateUrl('frontend_payment_skrill_cancel', array(), true),
            'BACKURL' => $this->generateUrl('frontend_payment_back_url', array(), true),
            'catalogUrl' => $this->generateUrl('frontend_search_ownership', array(), true),
            'homeUrl' => "https://www.mycasaparticular.com",
            'CN' => $user->getName(),
            'PM' =>  'CreditCard',
            'BRAND' =>  $method,
            'EMAIL' => $user->getUserEmail(),
            'logo_url' => $logoUrl,
            'first_name' => $user->getName(),
            'last_name' => $user->getUserLastName(),
            'owneraddress' => $user->getUserAddress(),
            'ownerZIP' => $userTourist->getUserTouristPostalCode(),
            'ownertown' => $user->getUserCity(),
            'ownercty' => $user->getUserCountry()->getCoCode(),
            'ownertelno' => $user->getUserPhone(),
            'COM' => 'MyCasaParticular.com',
            'SHASign' => $sha1, //"3w2bWAgHt147852veiuno*"
            'PM_list_type' => "VISA, Mastercard",
            'button_text' => $translator->trans('SKRILL_PAY_WITH_SKRILL')
        );

        $postFinanceDetails = $this->getSkrillDetailsData($bookingId);
        $postFinanceData = array_merge($postFinanceData, $postFinanceDetails);

        $this->log(date('Y-m-d H:i:s') . ': PaymentController line ' . __LINE__ .
            ": Data sent to Skrill: \n" . print_r($postFinanceData, true));

        return $postFinanceData;*/
    }



    private function getSkrillViewData(booking $booking, user $user, userTourist $userTourist)
    {
        $bookingId = $booking->getBookingId();
        $translator = $this->get('translator');
        $locale = $this->getRequest()->getLocale();
        $relativeLogoUrl = $this->container->get('templating.helper.assets')->getUrl('bundles/frontend/img/mycp.png');
        $logoUrl = $this->getRequest()->getSchemeAndHttpHost() . $relativeLogoUrl;

        $skrillData = array(
            'action_url' => self::$skrillPostUrl,
            'pay_to_email' => 'accounting@mycasaparticular.com',
            'recipient_description' => 'MyCasaParticular.com',
            'transaction_id' => $bookingId,
            'return_url' => $this->generateUrl('frontend_payment_skrill_return', array('bookingId' => $bookingId), true),
            'return_url_text' => $translator->trans('SKRILL_RETURN_TO_MYCP'),
            'cancel_url' => $this->generateUrl('frontend_payment_skrill_cancel', array(), true),
            'status_url' => $this->generateUrl('frontend_payment_skrill_status', array(), true),
            'status_url2' => 'booking@mycasaparticular.com',
            'language' => SkrillHelper::getSkrillLanguageFromLocale($locale),
            'confirmation_note' => $translator->trans('SKRILL_CONFIRMATION_NOTE'),
            'pay_from_email' => $user->getUserEmail(),
            'logo_url' => $logoUrl,
            'first_name' => $user->getName(),
            'last_name' => $user->getUserLastName(),
            'address' => $user->getUserAddress(),
            'postal_code' => $userTourist->getUserTouristPostalCode(),
            'city' => $user->getUserCity(),
            'country' => $user->getUserCountry()->getCoCode(),
            'amount' => $booking->getBookingPrepay(),
            'currency' => $booking->getBookingCurrency()->getCurrCode(),
            'payment_methods' => 'ACC,DID,SFT',
            'button_text' => $translator->trans('SKRILL_PAY_WITH_SKRILL')
        );

        $skrillDetails = $this->getSkrillDetailsData($bookingId);
        $skrillData = array_merge($skrillData, $skrillDetails);

        $this->log(date('Y-m-d H:i:s') . ': PaymentController line ' . __LINE__ .
            ": Data sent to Skrill: \n" . print_r($skrillData, true));

        return $skrillData;
    }

    /**
     * Returns the Details section of the POST data to be sent to Skrill.
     *
     * @param $bookingId
     * @return array
     */
    private function getSkrillDetailsData($bookingId)
    {
        $em = $this->getDoctrine()->getManager();

        $reservations = $em
            ->getRepository('mycpBundle:ownershipReservation')
            ->findBy(array('own_res_reservation_booking' => $bookingId));

        $generalReservationIds = array();

        foreach($reservations as $reservation) {
            $generalReservation = $reservation->getOwnResGenResId();

            if(empty($generalReservation)) {
                continue;
            }

            $generalReservationId = $generalReservation->getGenResId();

            if(!in_array($generalReservationId, $generalReservationIds)) {
                $generalReservationIds[] = $generalReservationId;
            }
        }

        return $this->generateDetailsFromGeneralReservations($bookingId, $generalReservationIds);
    }

    /**
     * Generates a Skrill details array from the given generalReservations
     *
     * @param $bookingId
     * @param array $generalReservationIds
     * @return array
     */
    private function generateDetailsFromGeneralReservations($bookingId, array $generalReservationIds = array())
    {
        $finalDetails = array(
            'detail1_description' => 'Booking ID:  ',
            'detail1_text' => $bookingId,
        );

        $finalDetailsString = '';
        $num = 2;
        $maxReached = false;

        foreach ($generalReservationIds as $generalReservationId) {

            if ($num > self::MAX_SKRILL_NUM_DETAILS) {
                $maxReached = true;
                break;
            }

            $detailString = \MyCp\FrontEndBundle\Helpers\ReservationHelper::getCASId($generalReservationId);

            if (strlen($finalDetailsString . ', ' . $detailString) > self::MAX_SKRILL_DETAIL_STRING_LENGTH) {
                $detail = $this->getSkrillDetail($num, $finalDetailsString);
                $finalDetails = array_merge($finalDetails, $detail);
                $finalDetailsString = $detailString;
                $num++;
            } else {
                $detailString = (empty($finalDetailsString) ? '' : ', ') . $detailString;
                $finalDetailsString .= $detailString;
            }
        }

        if (!$maxReached && !empty($finalDetailsString)) {
            $detail = $this->getSkrillDetail($num, $finalDetailsString);
            $finalDetails = array_merge($finalDetails, $detail);
        }

        return $finalDetails;
    }

    /**
     * Returns a valid Skrill Detail array
     *
     * @param $num
     * @param $text
     * @return array
     */
    private function getSkrillDetail($num, $text)
    {
        $detail = array(
            "detail{$num}_description" => 'Reservation IDs: ',
            "detail{$num}_text" => $text
        );

        return $detail;
    }

    private function log($message)
    {
        $path = $this->get('kernel')->getRootDir() . '/../app/logs/payment.log';
        file_put_contents($path, $message . PHP_EOL, FILE_APPEND);
    }

}

