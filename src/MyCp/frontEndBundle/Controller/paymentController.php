<?php

namespace MyCp\FrontendBundle\Controller;

use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use MyCp\frontEndBundle\Helpers\PaymentHelper;
use MyCp\frontEndBundle\Helpers\SkrillHelper;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\payment;
use MyCp\mycpBundle\Entity\skrillPayment;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\userTourist;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class paymentController extends Controller {

    private static $skrillPostUrl = 'https://www.moneybookers.com/app/payment.pl';

    public function skrillPaymentAction($bookingId) {
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

        $userTourist = $em->getRepository('mycpBundle:userTourist')
                ->findOneBy(array('user_tourist_user' => $user->getUserId()));

        if (empty($userTourist)) {
            throw new EntityNotFoundException("userTourist($userTourist)");
        }

        $skrillData = $this->getSkrillViewData($booking, $user, $userTourist);

        return $this->render('frontEndBundle:payment:skrillPayment.html.twig', $skrillData);
    }

    public function skrillReturnAction($bookingId) {
        $booking = $this->getBookingFrom($bookingId);

        if (empty($booking)) {
            throw new InvalidParameterException($bookingId);
        }

        $pollingUrl = $this->generateUrl('frontend_payment_poll_payment', array('bookingId' => $bookingId), true);
        $confirmationUrl = $this->generateUrl('frontend_confirmation_reservation', array('id_booking' => $bookingId), true); // $this->generateUrl('frontend_payment_skrill_test_response', array('status' => 'Confirmation'), true);
        $timeoutUrl = $this->generateUrl('frontend_payment_skrill_test_response', array('status' => 'Timeout'), true);
        $cancelUrl = $this->generateUrl('frontend_payment_skrill_test_response', array('status' => 'Cancelled by status response'), true);
        $pendingUrl = $this->generateUrl('frontend_payment_skrill_test_response', array('status' => 'Pending'), true);
        $failedUrl = $this->generateUrl('frontend_payment_skrill_test_response', array('status' => 'Failed'), true);

        //

        return $this->render(
                        'frontEndBundle:payment:waitingForPayment.html.twig', array(
                    'pollingUrl' => $pollingUrl,
                    'confirmationUrl' => $confirmationUrl,
                    'timeoutUrl' => $timeoutUrl,
                    'cancelUrl' => $cancelUrl,
                    'pendingUrl' => $pendingUrl,
                    'failedUrl' => $failedUrl,
                    'timeoutTicks' => 80,
                    'pollingPeriodMsec' => 1000
        ));
    }

    public function pollPaymentAction($bookingId) {
        $booking = $this->getBookingFrom($bookingId);

        if (empty($booking)) {
            return new JsonResponse(array('status' => 'booking_not_found'));
        }

        $payment = $this->getPaymentFrom($booking);

        if (empty($payment)) {
            return new JsonResponse(array('status' => 'payment_not_found'));
        }

        switch ($payment->getStatus()) {
            case PaymentHelper::STATUS_SUCCESS: return new JsonResponse(array('status' => 'payment_found'));
            case PaymentHelper::STATUS_FAILED: return new JsonResponse(array('status' => 'payment_failed'));
            case PaymentHelper::STATUS_CANCELLED: return new JsonResponse(array('status' => 'payment_cancelled'));
            default: return new JsonResponse(array('status' => 'payment_pending'));
        }
    }

    public function skrillStatusAction() {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest()->request->all();

        $this->log(date('Y-m-d H:i:s') . ': PaymentController line ' . __LINE__ . ': Received Skrill status.');

        if (empty($request)) {
            return new Response('Empty post data', 400);
        }

        $this->log('PaymentController line ' . __LINE__ . "Request: \n" . print_r($request, true));

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
            $this->log(date(DATE_RSS) . ' - PaymentController line ' . __LINE__ . ': Currency ' . $currencyIsoCode . ' not found.');
            return new Response('', 200);
        }

        $payment->setCurrency($currency);
        $payment->setModified(new DateTime());
        $payment->setStatus(SkrillHelper::getInternalStatusCodeFrom($skrillPayment->getStatus()));

        $skrillPayment->setPayment($payment);
        $em->persist($payment);
        $em->persist($skrillPayment);
        $em->flush();

        $this->log(date(DATE_RSS) . ' - PaymentController line ' . __LINE__ . ': Payment ID: ' . $payment->getId() . "\nSkrillRequest ID: " . $skrillPayment->getId());

        return new Response('Thanks', 200);
    }

    public function skrillCancelAction() {
        // TODO: redirect to booking/cancelled_payment page

        return $this->render(
                        'frontEndBundle:payment:skrillResponseTest.html.twig', array('status' => 'Cancelled by Skrill'));
    }

    public function skrillTestResponseAction($status) {
        // TODO: this function is just for testing Skrill responses

        return $this->render(
                        'frontEndBundle:payment:skrillResponseTest.html.twig', array('status' => $status));
    }

    // TODO: With this function the skrill payment can be tested instead of using the real button
    // on the reservation page
    public function skrillTestPaymentAction($bookingId = 0) {
        $payUrl = $this->generateUrl('frontend_payment_skrill', array('bookingId' => $bookingId), true);
        return $this->render(
                        'frontEndBundle:payment:skrillPaymentTest.html.twig', array('payUrl' => $payUrl));
    }

    // TODO: this function emulates the POST status request from Skrill. Can be deleted when not needed anymore
    public function skrillSendTestPostRequestAction($bookingId, $status) {
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
        curl_exec($ch);

        return $this->redirect($this->generateUrl(
                                'frontend_payment_skrill_return', array('bookingId' => $bookingId)
        ));
    }

    private function getBookingFrom($bookingId) {
        try {
            return $this->getDoctrine()
                            ->getRepository('mycpBundle:booking')
                            ->find($bookingId);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getPaymentFrom($booking) {
        try {
            return $this->getDoctrine()
                            ->getRepository('mycpBundle:payment')
                            ->findOneBy(array('booking' => $booking));
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getCurrencyFrom($currencyIsoCode) {
        $currencyIsoCode = strtoupper(trim($currencyIsoCode));

        try {
            return $this->getDoctrine()
                            ->getRepository('mycpBundle:currency')
                            ->findOneBy(array('curr_code' => $currencyIsoCode));
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getSkrillViewData(booking $booking, user $user, userTourist $userTourist) {
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
            'first_name' => $user->getUserName(),
            'last_name' => $user->getUserLastName(),
            'address' => $user->getUserAddress(),
            'postal_code' => $userTourist->getUserTouristPostalCode(),
            'city' => $user->getUserCity(),
            'country' => $user->getUserCountry()->getCoCode(),
            'amount' => $booking->getBookingPrepay(),
            'currency' => $booking->getBookingCurrency()->getCurrCode(),
            'detail1_description' => $translator->trans('SKRILL_DESCRIPTION_BOOKING_ID'),
            'detail1_text' => $bookingId,
            'payment_methods' => 'ACC,DID,SFT',
            'button_text' => $translator->trans('SKRILL_PAY_WITH_SKRILL')
        );

        $this->log(date('Y-m-d H:i:s') . ': PaymentController line ' . __LINE__ . ": Data sent to Skrill: \n" . print_r($skrillData, true));

        return $skrillData;
    }

    private function log($message) {
        $path = $this->get('kernel')->getRootDir() . '/../app/logs/payment.log';
        file_put_contents($path, $message . PHP_EOL, FILE_APPEND);
    }

}

