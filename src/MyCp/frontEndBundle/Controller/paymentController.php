<?php

namespace MyCp\FrontendBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class paymentController extends Controller {

    public function skrillAction()
    {

        // TODO: get all the booking information from DB and publish to view
        //$url = $this->generateUrl('skrillReturn');
        $skrillData = array(
            'name' => 'Jakob',
            'action_url' => 'https://www.moneybookers.com/app/payment.pl',
            'pay_to_email' => 'a@b.de', // ABUC email
            'recipient_description' => 'MyCasaParticular',
            'transaction_id' => 'abuc_transaction_id',
            'return_url' => $this->generateUrl('frontend_payment_skrill_return', array(), true),
            'return_url_text' => 'Return to MyCasaParticular',
            'cancel_url' => $this->generateUrl('frontend_payment_skrill_cancel', array(), true),
            'status_url' => $this->generateUrl('frontend_payment_skrill_status', array(), true),
            'status_url2' => 'booking@mycasaparticular.com',
            'language' => 'EN',
            'confirmation_note' => 'Thank you for booking with MyCasaParticular!',
            'pay_from_email' => 'customer@customer.com', // customer email
            'logo_url' => '',



    );


        return $this->render('frontEndBundle:Payment:skrill.html.twig', $skrillData);
    }

    public function skrillReturnAction()
    {

    }

    public function skrillStatusAction()
    {

        $request = $this->getRequest()->request;
        $email = $request->get('pay_to_email'); //all();

        ob_start();
        var_dump($request);
        $dump = ob_get_clean();

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
}