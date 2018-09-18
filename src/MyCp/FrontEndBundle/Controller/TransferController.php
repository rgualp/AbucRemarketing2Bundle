<?php

namespace MyCp\FrontEndBundle\Controller;

use Abuc\RemarketingBundle\Event\JobEvent;
use MyCp\mycpBundle\Entity\bookingModality;
use MyCp\mycpBundle\Entity\cart;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MyCp\mycpBundle\Entity\transfer;
class TransferController extends Controller {


    public function getMainTransferFormAction()
    {
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        $em = $this->getDoctrine()->getManager();
        $transfers= $em->getRepository('mycpBundle:transfer')->findAll();



        if ($mobileDetector->isMobile()) {
            return $this->render('@MyCpMobileFrontend/transfer/transfer.html.twig', array(
            'transfer'=>$transfers
            ));
        }
        else {
            return $this->render('FrontEndBundle:transfer:transfers.html.twig', array(
                'transfer'=>$transfers
            ));
        }
    }

    public function SendTransferRequestAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $name=$check_dispo=$request->get('name');
        $lastname=$check_dispo=$request->get('lastname');
        $email=$check_dispo=$request->get('email');
        $transfer=$em->getRepository('mycpBundle:transfer')->find($request->get('transfer'));
        $pax=$request->get('pax');
        $time=$request->get('date');
        $comment=$request->get('comment');
        $veiculo=$request->get('transferMethod');
        $address=$request->get('address');
        $hora=$request->get('hora');

        if($veiculo==1){
            $veiculo='Privado';

        }
        else{
            $veiculo='Ban';
        }
        $contactService = $this->get('front_end.services.contact');
        try {
            $contactService->sendTransferContact($hora,$address,$time,$name,$lastname,$pax,$transfer->getFrom(),$transfer->getTo(),$veiculo,$email,$comment);
        } catch (\Exception $e) {
            return new Response(0);
        }
        return new Response(1);


    }

}
