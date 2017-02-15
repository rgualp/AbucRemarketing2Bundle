<?php

namespace MyCp\PartnerBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use MyCp\FrontEndBundle\Helpers\Time;
use MyCp\PartnerBundle\Entity\paPendingPaymentAccommodation;
use MyCp\PartnerBundle\Entity\paPendingPaymentAgency;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CancelReservationService extends Controller
{
    /**
     * @var ObjectManager
     */
    private $em;

    private $timer;
    private $mailService;
    private $templating;
    protected $container;

    public function __construct(ObjectManager $em, Time $timer, $container, $mailService, $templating)
    {
        $this->em = $em;
        $this->timer = $timer;
        $this->container = $container;
        $this->mailService = $mailService;
        $this->templating = $templating;
    }

    public function calculatePendingPayments($isCancelFromAgency, $cancelDate, $travelAgency, $generalReservation, $booking, $firstNightPrice, $totalInSite, $totalNights, $totalRooms, $cancelPayment)
    {
        $em = $this->getDoctrine()->getManager();
        $pendingPaymentStatusPending = $em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_name" => "pendingPayment_pending_status", "nom_category" => "paymentPendingStatus"));
        $cancelPaymentType= $em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_name" => "cancel_payment_accommodation", "nom_category" => "paymentPendingType"));

        $refunds = $this->getRefunds($isCancelFromAgency, $cancelDate, $generalReservation, ($travelAgency->getCommission() / 100), $totalInSite, $totalNights, $totalRooms, $firstNightPrice);
        $agencyRefund = $refunds["agencyRefund"];
        $accommodationRefund = $refunds["accommodationRefund"];

        $this->updateCompletePayment($generalReservation, $travelAgency, $agencyRefund);
        $this->createAgencyPendingPayment($generalReservation, $travelAgency, $agencyRefund, $booking, $cancelPayment, $cancelPaymentType);
        $this->createAccommodationPendingPayment($generalReservation, $travelAgency, $accommodationRefund, $booking, $cancelPayment, $cancelPaymentType, $pendingPaymentStatusPending);

        $this->em->flush();

        //Enviar correos a Nataly, Sarahy y Andy
        $this->sendTeamCancelReservation($travelAgency, $agencyRefund, $generalReservation, $booking);
    }

    private function getRefunds($isCancelFromAgency, $cancelDate, $generalReservation, $agencyCommission, $totalInSite, $totalNights, $totalRooms, $firstNightPrice)
    {
        $serviceFee = $generalReservation->getServiceFee();

        $totalDiffDays = $this->timer->diffInDays($cancelDate->format("Y-m-d"), $generalReservation->getGenResFromDate()->format("Y-m-d"));

        //Tarifa fija
        $tf = $generalReservation->getServiceFee()->getFixedFee();

        //Tarifa de agencia
        $tag = $totalInSite * $this->em->getRepository("mycpBundle:serviceFee")->calculateTouristServiceFee($totalRooms, $totalNights, ($totalInSite / $totalNights),$serviceFee->getId());

        //Comision de agencia
        $ca = $agencyCommission * $totalInSite;
        $firstNightCost = $firstNightPrice * (1 - $generalReservation->getGenResOwnId()->getOwnCommissionPercent() / 100);

        if($totalDiffDays > 7){
            return ($isCancelFromAgency) ? array("agencyRefund" => ($totalInSite + $tf - $ca), "accommodationRefund" => 0) : array("agencyRefund" => ($totalInSite + $tag + $tf - $ca), "accommodationRefund" => 0);
        }
        else{
            return  ($isCancelFromAgency) ? array("agencyRefund" => ($totalInSite - $ca - $firstNightPrice), "accommodationRefund" => $firstNightCost) : array("agencyRefund" => ($totalInSite + $tag + $tf - $ca), "accommodationRefund" => $firstNightCost);
        }
    }

    private function updateCompletePayment($generalReservation, $travelAgency, $agencyRefund)
    {
        $cancelPendingPaymentStatus = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array(
            "nom_name" => "pendingPayment_canceled_status",
            "nom_category" => "paymentPendingStatus"
        ));

        $completePaymentType = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array(
            "nom_name" => "complete_payment",
            "nom_category" => "paymentPendingType"
        ));

        //Modificar pago completo. Si el monto a cancelar es el mismo que tiene el pago, se cancela
        $completePayment = $this->em->getRepository("PartnerBundle:paPendingPaymentAccommodation")->findOneBy(array(
            "reservation" => $generalReservation,
            "agency" => $travelAgency,
            "type" => $completePaymentType
        ));

        $newPayment = $completePayment->getAmount() - $agencyRefund;

        if($newPayment <= 0) {
            $completePayment->setStatus($cancelPendingPaymentStatus);
        } else {
            $completePayment->setAmount($newPayment);
        }
        $this->em->persist($completePayment);
    }

    private function createAgencyPendingPayment($generalReservation, $travelAgency, $agencyRefund, $booking, $cancelPayment, $cancelPaymentType){
        //Craer un pago pendiente de agencia
        $paymentSkrill = $this->em->getRepository("mycpBundle:payment")->findOneBy(array("booking" => $booking->getBookingId()));
        $pendingStatus = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array(
            "nom_name" => "pendingPayment_pending_status",
            "nom_category" => "paymentPendingStatus"
        ));

        $payDate = new \DateTime();
        $payDate->add(new \DateInterval('P1D'));

        $payment = new paPendingPaymentAgency();
        $payment->setBooking($booking);
        $payment->setAmount($agencyRefund * $paymentSkrill->getCurrentCucChangeRate());
        $payment->setAgency($travelAgency);
        $payment->setReservation($generalReservation);
        $payment->setCreatedDate(new \DateTime());
        $payment->setType($cancelPaymentType);
        $payment->setStatus($pendingStatus);
        $payment->setCancelPayment($cancelPayment);
        $payment->setPayDate($payDate);
        $this->em->persist($payment);
    }

    private function createAccommodationPendingPayment($generalReservation, $travelAgency, $accommodationRefund, $booking, $cancelPayment, $cancelPaymentType, $pendingPaymentStatusPending)
    {
        //Crear un pago pendiente a propietario por motivo de cancelacion
        if($accommodationRefund > 0) {
            $payDate = $generalReservation->getGenResToDate();
            $payDate->add(new \DateInterval('P3D'));

            $pendingPayment = new paPendingPaymentAccommodation();
            $pendingPayment->setReservation($generalReservation);
            $pendingPayment->setPayDate($payDate);
            $pendingPayment->setCreatedDate(new \DateTime());
            $pendingPayment->setAgency($travelAgency);
            $pendingPayment->setAmount($accommodationRefund);
            $pendingPayment->setBooking($booking);
            $pendingPayment->setStatus($pendingPaymentStatusPending);
            $pendingPayment->setType($cancelPaymentType);
            $pendingPayment->setAccommodation($generalReservation->getGenResOwnId());
            $pendingPayment->setCancelPayment($cancelPayment);
            $this->em->persist($pendingPayment);
        }
    }

    private function sendTeamCancelReservation($travelAgency, $agencyRefund, $generalReservation, $booking)
    {
        $emailBody = $this->templating->renderResponse('FrontEndBundle:mails:rt_agency_cancel.html.twig', array(
            'travelAgency' => $travelAgency,
            'reservation' => $generalReservation,
            'booking' => $booking,
            'refund' => $agencyRefund
        ));

        $this->mailService->setTo(array(/*'sarahy_amor@yahoo.com', 'reservation@mycasaparticular.com', 'andy@hds.li'*/"yanet.moralesr@gmail.com"));
        $this->mailService->setSubject("Cancelación de Agencia");
        $this->mailService->setFrom("reservation@mycasaparticular.com", 'MyCasaParticular.com');
        $this->mailService->setBody($emailBody->getContent());
        $this->mailService->setEmailType("PARTNER_CANCEL");

        return $this->mailService->sendEmail();
    }
}
