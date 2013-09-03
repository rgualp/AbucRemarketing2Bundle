<?php

namespace MyCp\FrontendBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use MyCp\frontEndBundle\Helpers\SkrillStatusResponse;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\user;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class paymentController extends Controller {

    public function skrillAction($reservationId = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->find('generalReservation', $reservationId);

        if(empty($reservation) || !($reservation instanceof generalReservation)) {
            throw new EntityNotFoundException("generalReservation($reservationId)");
        }

        $user = $reservation->getUser();

        if(empty($user) || !($user instanceof user)) {
            throw new EntityNotFoundException("user($userId)");
        }

        $loggedInUser = $this->get('security.context')->getToken()->getUser();

        if($user->getUserId() !== $loggedInUser->getUserId()) {
            throw new AuthenticationException('Access to resource not permitted.');
        }

        $country = $user->getCountry()->getCode();

        // TODO: get all the booking information from DB and publish to view
        //$url = $this->generateUrl('skrillReturn');

        $skrillData = $this->getSkrillViewData($reservation, $user);

        return $this->render('frontEndBundle:Payment:skrill.html.twig', $skrillData);
    }

    public function skrillReturnAction()
    {

    }

    public function skrillStatusAction()
    {

        $request = $this->getRequest()->request->all();
        $skrillRequest = new SkrillStatusResponse($request);

        ob_start();
        //var_dump($request);
        var_dump($skrillRequest);
        $dump = ob_get_clean();

        // TODO: write answer POST values to database and return http status 200
        //return new Response('', 200);
        return $this->render(
            'frontEndBundle:Payment:skrill.html.twig',
            array('name' => 'Return', 'postRequest' => $dump));
    }

    public function skrillCancelAction()
    {

        return $this->render(
            'frontEndBundle:Payment:skrill.html.twig',
            array('name' => 'Cancel'));
    }

    private function getSkrillViewData(generalReservation $reservation, user $user)
    {

        return array(
            'action_url' => 'https://www.moneybookers.com/app/payment.pl',
            'pay_to_email' => 'a@b.de', // ABUC email
            'recipient_description' => 'MyCasaParticular.com',
            'transaction_id' => 'abuc_transaction_id',
            'return_url' => $this->generateUrl('frontend_payment_skrill_return', array(), true),
            'return_url_text' => 'Return to MyCasaParticular',
            'cancel_url' => $this->generateUrl('frontend_payment_skrill_cancel', array(), true),
            'status_url' => $this->generateUrl('frontend_payment_skrill_status', array(), true),
            'status_url2' => 'booking@mycasaparticular.com',
            'language' => 'EN',
            'confirmation_note' => 'MyCasaParticular wishes you pleasure visiting Cuba!',
            'pay_from_email' => 'customer@lkjldasjfljkadf.com', // customer email
            'logo_url' => '',//'http://www.mypaladar.com/mycp/mycpres/web/bundles/frontend/images/logo.png', // TODO: $this->getRequest()->getUriForPath('bundles/frontend/images/logo.png') //;'bundles/frontend/images/logo.png',
            'first_name' => 'John',
            'last_name' => 'Payer',
            'address' => '11 Payerstr St',
            'postal_code' => 'EC45MQ',
            'city' => 'Payertown',
            'country' => 'GBR', // TODO: wo die Liste der erlaubten countries speichern?! -> country überhaupt nötig?!
            'amount' => '0.75',
            'currency' => 'EUR', // TODO: Liste der Währungen
            'detail1_description' => 'ID Oferta:  ',
            'detail1_text' => 'CAS.1000xxx',
            'detail2_description' => 'Casa:  ',
            'detail2_text' => '1234',
            'detail3_description' => 'Description:',
            'detail3_text' => 'Rooms: 1 - Arrival: 10.10.2013 - Nights: 5',
            'detail4_description' => 'Description:',
            'detail4_text' => 'Adults: 2 - Children: 1',
            'payment_methods' => 'ACC,DID,SFT',
            'button_text' => 'Pay With Skrill'
        );
    }
}