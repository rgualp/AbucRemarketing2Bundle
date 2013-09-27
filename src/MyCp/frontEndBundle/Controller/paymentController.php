<?php

namespace MyCp\FrontendBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
//use MyCp\frontEndBundle\Helpers\SkrillStatusResponse;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use MyCp\frontEndBundle\Helpers\PaymentHelper;
use MyCp\frontEndBundle\Helpers\SkrillHelper;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\payment;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\skrillPayment;
use MyCp\mycpBundle\Entity\userTourist;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Exception\NotValidException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class paymentController extends Controller {

    private static $skrillPostUrl = 'https://www.moneybookers.com/app/payment.pl';

    public function skrillPaymentAction($reservationId)
    {
        $reservation = $this->getReservationFrom($reservationId);

        if(empty($reservation)) {
            throw new EntityNotFoundException($reservationId);
        }

        $user = $reservation->getGenResUserId();

        if(empty($user)) {
            throw new EntityNotFoundException("user($user)");
        }

        $loggedInUser = $this->get('security.context')->getToken()->getUser();

        if($user->getUserId() !== $loggedInUser->getUserId()) {
            throw new AuthenticationException('Access to resource not permitted.');
        }

        $userTourist = $this->getDoctrine()->getManager()->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));

        if(empty($userTourist)) {
            throw new EntityNotFoundException("userTourist($userTourist)");
        }

        $ownership = $this->getOwnershipOf($reservation);

        if(empty($ownership)) {
            throw new EntityNotFoundException("ownership($ownership)");
        }

        $skrillData = $this->getSkrillViewData($reservation, $user, $userTourist, $ownership);

        return $this->render('frontEndBundle:payment:skrillPayment.html.twig', $skrillData);
    }

    public function skrillReturnAction($reservationId)
    {
        if($this->getReservationFrom($reservationId) === false) {
            throw new InvalidParameterException($reservationId);
        }

        $pollingUrl = $this->generateUrl('frontend_payment_poll_payment', array('reservationId' => $reservationId), true);
        $confirmationUrl = $this->generateUrl('frontend_payment_skrill_test_response', array('status' => 'Confirmation'), true);
        $timeoutUrl = $this->generateUrl('frontend_payment_skrill_test_response', array('status' => 'Timeout'), true);
        $cancelUrl = $this->generateUrl('frontend_payment_skrill_test_response', array('status' => 'Cancelled by status response'), true);
        $pendingUrl = $this->generateUrl('frontend_payment_skrill_test_response', array('status' => 'Pending'), true);
        $failedUrl = $this->generateUrl('frontend_payment_skrill_test_response', array('status' => 'Failed'), true);

        return $this->render(
            'frontEndBundle:payment:waitingForPayment.html.twig',
            array(
                'pollingUrl' => $pollingUrl,
                'confirmationUrl' => $confirmationUrl,
                'timeoutUrl' => $timeoutUrl,
                'cancelUrl' => $cancelUrl,
                'pendingUrl' => $pendingUrl,
                'failedUrl' => $failedUrl,
                'timeoutTicks' => 60,
                'pollingPeriodMsec' => 1000
            ));
    }

    public function pollPaymentAction($reservationId)
    {
        $reservation = $this->getReservationFrom($reservationId);

        if(empty($reservation)) {
            throw new InvalidParameterException($reservationId);
            //return new JsonResponse(array('status' => 'reservation_not_found'));
        }

        $payment = $this->getPaymentOf($reservation);

        if(empty($payment)) {
            return new JsonResponse(array('status' => 'payment_not_found'));
        }

        $status = $payment->getStatus();

        if($status == PaymentHelper::STATUS_SUCCESS) {
            return new JsonResponse(array('status' => 'payment_found'));
        } elseif ($status == PaymentHelper::STATUS_FAILED) {
            return new JsonResponse(array('status' => 'payment_failed'));
        } elseif ($status == PaymentHelper::STATUS_CANCELLED) {
            return new JsonResponse(array('status' => 'payment_cancelled'));
        } else {
            return new JsonResponse(array('status' => 'payment_pending'));
        }
    }

    public function skrillStatusAction()
    {
        $em = $this->getDoctrine()->getManager();

        $request = $this->getRequest()->request->all();
        $skrillRequest = new skrillPayment($request);

        $reservationId = $skrillRequest->getMerchantTransactionId();
        $reservation = $this->getReservationFrom($reservationId);

        if($reservation === false) {
            $this->log('PaymentController line '.__LINE__.': Reservation (id='.$reservationId.') not found.');
            return new Response('', 200);
        }

        $payment = $this->getPaymentOf($reservation);

        if(empty($payment)) {
            $payment = new payment();
            $payment->setCreated(new \DateTime());
            $payment->setGeneralReservation($reservation);
        }

        $payment->setPayedAmountFromString($skrillRequest->getPayedAmount());

        $skrillCurrency = $skrillRequest->getSkrillCurrency();
        $currency = $this->getCurrencyFrom($skrillCurrency);

        if(empty($currency)) {
            $this->log(date(DATE_RSS).' - PaymentController line '.__LINE__.': Currency '.$skrillCurrency.' not found.');
            return new Response('', 200);
        }

        $payment->setCurrency($currency);
        $payment->setModified(new \DateTime());
        $payment->setStatus(SkrillHelper::getInternalStatusCodeFrom($skrillRequest->getStatus()));

        $skrillRequest->setPayment($payment);

        $em->persist($payment);
        $em->flush();
        $em->persist($skrillRequest);
        $em->flush();

        $this->log(date(DATE_RSS).' - PaymentController line '.__LINE__.': '. json_encode($payment) . "\n\n".json_encode($skrillRequest));

        return new Response('Thanks', 200);
    }

    public function skrillCancelAction()
    {
        // TODO: redirect to reservation/cancelled_payment page

        return $this->render(
            'frontEndBundle:payment:skrillResponseTest.html.twig',
            array('status' => 'Cancelled by Skrill'));
    }

    public function skrillTestResponseAction($status)
    {
        // TODO: this function is just for testing Skrill responses

        return $this->render(
            'frontEndBundle:payment:skrillResponseTest.html.twig',
            array('status' => $status));
    }

    // TODO: With this function the skrill payment can be tested instead of using the real button
    // on the reservation page
    public function skrillTestPaymentAction($reservationId = 0)
    {
        $payUrl = $this->generateUrl('frontend_payment_skrill', array('reservationId' => $reservationId), true);
        return $this->render(
            'frontEndBundle:payment:skrillPaymentTest.html.twig',
            array('payUrl' => $payUrl));
    }

    // TODO: this function emulates the POST status request from Skrill. Can be deleted when not needed anymore
    public function skrillSendTestPostRequestAction($reservationId, $status)
    {
        $urltopost = $this->generateUrl('frontend_payment_skrill_status', array(), true);
        $datatopost = array (
            "mb_transaction_id" => "ABCD",
            "transaction_id" => $reservationId,
            "pay_to_email" => "accounting@mycasaparticular.com",
            "pay_from_email" => "customer@email.com",
            "mb_amount" => "12.12",
            "mb_currency" => "EUR",
            "status" => $status,
            "merchant_id" => 22512989,

        );

        $ch = curl_init ($urltopost);
        curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $datatopost);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec ($ch);

        return $this->redirect($this->generateUrl(
            'frontend_payment_skrill_return',
            array('reservationId' => $reservationId)
        ));
    }

    private function getReservationFrom($reservationId)
    {
        if(empty($reservationId) || !is_numeric($reservationId) || (int)$reservationId <= 0) {
            return null;
        }

        $em = $this->getDoctrine()->getManager();

        try {
            $reservation = $em->getRepository('mycpBundle:generalReservation')->find($reservationId);
        } catch (\Exception $e) {
            return null;
        }

        return $reservation;
    }

    private function getPaymentOf($reservation)
    {
        $repository = $this->getDoctrine()->getRepository('mycpBundle:payment');
        $payment = null;

        try {
            $payment = $repository->findOneBy(array('general_reservation' => $reservation));
        } catch (\Exception $e) {
            return null;
        }

        return $payment;
    }

    private function getOwnershipOf(generalReservation $reservation)
    {
        $ownership = null;

        try {
            $ownership = $reservation->getGenResOwnId();
        } catch (\Exception $e) {
            return null;
        }

        return $ownership;
    }

    private function getCurrencyFrom($skrillCurrency)
    {
        $skrillCurrency = strtolower(trim($skrillCurrency));

        $currency = null;

        try {
            $repo = $this->getDoctrine()->getRepository('mycpBundle:currency');
            $currency = $repo->findOneBy(array('curr_code' => $skrillCurrency));
        } catch (\Exception $e) {
            return null;
        }

        return $currency;
    }

    private function getSkrillViewData(generalReservation $reservation, user $user, userTourist $userTourist, ownership $casa)
    {
        $reservationId = $reservation->getGenResId();
        $translator = $this->get('translator');
        $locale = $this->getRequest()->getLocale();

        return array(
            'action_url' => self::$skrillPostUrl,
            'pay_to_email' => 'accounting@mycasaparticular.com',
            'recipient_description' => 'MyCasaParticular.com',
            'transaction_id' => $reservationId,
            'return_url' => $this->generateUrl('frontend_payment_skrill_return', array('reservationId' => $reservationId), true),
            'return_url_text' => $translator->trans('SKRILL_RETURN_TO_MYCP'),
            'cancel_url' => $this->generateUrl('frontend_payment_skrill_cancel', array(), true),
            'status_url' => $this->generateUrl('frontend_payment_skrill_status', array(), true),
            'status_url2' => 'booking@mycasaparticular.com',
            'language' => SkrillHelper::getSkrillLanguageFromLocale($locale),
            'confirmation_note' => $translator->trans('SKRILL_CONFIRMATION_NOTE'),
            'pay_from_email' => $user->getUserEmail(),
            'logo_url' => 'http://www.mypaladar.com/mycp/mycpres/web/bundles/frontend/images/logo.png', // TODO: $this->getRequest()->getUriForPath('bundles/frontend/images/logo.png') //;'bundles/frontend/images/logo.png',
            'first_name' => $user->getUserName(),
            'last_name' => $user->getUserLastName(),
            'address' => $user->getUserAddress(),
            'postal_code' => $userTourist->getUserTouristPostalCode(),
            'city' => $user->getUserCity(),
            'country' => $user->getUserCountry()->getCoCode(),
            'amount' => '', // TODO: Price stored in ownershipReservation not generalReservation! $reservation->getTotalPriceInSiteAsString(),// '0.5',
            'currency' => $userTourist->getUserTouristCurrency()->getCurrCode(),
            'detail1_description' => $translator->trans('SKRILL_DESCRIPTION_BOOKING_ID'),
            'detail1_text' => $reservationId,
            'detail2_description' => isset($casa) ? 'Casa:  ' : '', // TODO: fliegt wahrscheinlich raus, da Zahlung unabhängig vom Casa sein soll
            'detail2_text' => isset($casa) ? $casa->getOwnMcpCode() : '',
            'payment_methods' => 'ACC,DID,SFT',
            'button_text' => $translator->trans('SKRILL_PAY_WITH_SKRILL')
        );
    }

    private function log($message)
    {
        $path = $this->get('kernel')->getRootDir() . '/../app/logs/payment.log';
        file_put_contents($path, $message, FILE_APPEND);
    }

}