<?php

namespace MyCp\mycpBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use MyCp\mycpBundle\Entity\payment;
use MyCp\mycpBundle\Entity\skrillPayment;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use PhpImap;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;

class PaymentSyncService extends Controller{
    private $em;
    private $logger;
    private $mailServer;
    private $syncEmail;
    private $emailPassword;
    private $bookingService;

    public function __construct($em, $logger, $bookingService, $mailServer, $syncEmail, $emailPassword){
        $this->em = $em;
        $this->logger = $logger;
        $this->mailServer = $mailServer;
        $this->syncEmail = $syncEmail;
        $this->emailPassword = $emailPassword;
        $this->bookingService = $bookingService;
    }

    public function syncronize($sinceDate = null)
    {
        $mailbox = new PhpImap\Mailbox($this->mailServer, $this->syncEmail, $this->emailPassword, __DIR__);

        $date = date ( "d F Y", $sinceDate );

        if($sinceDate != null)
            $mailsIds = $mailbox->searchMailBox('SINCE "'.$date.'"');
        else
            $mailsIds = $mailbox->searchMailBox('ALL');


        foreach($mailsIds as $mailId){
            $mail = $mailbox->getMail($mailId);

            if($mail->fromAddress == "no_reply@skrill.com"){
                $id = intval(preg_replace('/[^0-9]+/', '', $mail->subject), 10);

                $booking = $this->em->getRepository("mycpBundle:booking")->find($id);
                $payment = $this->em->getRepository("mycpBundle:payment")->findOneBy(array("booking" => $id));

                if($payment == null && $booking != null) {
                    $payment = new payment();
                    $payment->setBooking($booking)
                        ->setCreated(new \DateTime())
                        ->setCurrency($booking->getBookingCurrency())
                        ->setCurrentCucChangeRate($booking->getBookingCurrency()->getCurrCucChange())
                        ->setModified(new \DateTime())
                        ->setPayedAmount($booking->getBookingPrepay())
                        ->setStatus(PaymentHelper::STATUS_SUCCESS);

                    $this->em->persist($payment);

                    $this->em->flush();

                    $this->bookingService->postProcessBookingPayment($payment);
                }
            }
        }
    }

    public function getAllToSync($sinceDate = null)
    {
        $bookings = array();

        $mailbox = new PhpImap\Mailbox($this->mailServer, $this->syncEmail, $this->emailPassword, __DIR__);

        $date = date ( "d F Y", $sinceDate );

        if($sinceDate != null)
            $mailsIds = $mailbox->searchMailBox('SINCE "'.$date.'"');
        else
            $mailsIds = $mailbox->searchMailBox('ALL');



        foreach($mailsIds as $mailId){
            $mail = $mailbox->getMail($mailId);

            if($mail->fromAddress == "no_reply@skrill.com") {
                $id = intval(preg_replace('/[^0-9]+/', '', $mail->subject), 10);

                $booking = $this->em->getRepository("mycpBundle:booking")->find($id);
                $payment = $this->em->getRepository("mycpBundle:payment")->findOneBy(array("booking" => $id));

                if($payment == null && $booking != null) {
                    $bookings[] = $booking;
                }

            }
        }

        return $bookings;
    }


}


