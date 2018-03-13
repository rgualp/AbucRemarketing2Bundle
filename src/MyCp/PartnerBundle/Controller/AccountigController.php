<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Form\restorePasswordUserType;
use MyCp\PartnerBundle\Entity\paPendingPaymentAccommodation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use MyCp\PartnerBundle\Entity\paTravelAgency;
use MyCp\PartnerBundle\Entity\paTourOperator;

use MyCp\PartnerBundle\Form\paTravelAgencyType;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\photo;
use MyCp\PartnerBundle\Entity\paAccountLedgers;
use Symfony\Component\HttpFoundation\Response;
use MyCp\FrontEndBundle\Form\registerTourOperatorType;
class AccountigController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */


   public function indexAccountingAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $agency = $tourOperator->getTravelAgency();
        $account =$agency->getAccount();
        $curr = $this->getCurr($request);
        $balance=round($account->getBalance()*$curr['change'],2);
        return new JsonResponse([
            'success' => true,

            'id' => 'id_dashboard_accounting',
            'html' => $this->renderView('PartnerBundle:Accounting:accounting_agency.html.twig',array('coin'=>$curr['code'],'balance'=>$balance)),
            'msg' => 'Vista accounting summary'


            ]);
    }
   public function getReservationsData($filters, $start, $limit, $draw, $status)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('mycpBundle:generalReservation');
        $userrepo= $em->getRepository('mycpBundle:user');
        $touroperators= array();

        $touroperators=$userrepo->getAllTourOperators($touroperators,$user);

        #region PAGINADO
        if($limit==false){

        }
        else{
            $page = ($start > 0) ? $start / $limit + 1 : 1;
        }

        $paginator = $repository->getReservationsPartnerAccountig($user->getUserId(), $status, $filters, $start, $limit,$touroperators);;
        $reservations = $paginator['data'];

        #endregion PAGINADO


        $data = $reservations;

        return $data;
    }
   public function  getPriceandBooking($reservation,$curr){
       $ownReservations = $reservation->getOwn_reservations();
       $timeService = $this->get('time');
        $arrTmp['data']['rooms'] = array();
        $totalPrice=0;
        $booking=$ownReservation = $ownReservations->first()->getOwnResReservationBooking()->getBookingId();
        if (!$ownReservations->isEmpty()) {
            $ownReservation = $ownReservations->first();

            do {
                $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());

                if ($ownReservation->getOwnResNightPrice() > 0) {
                    $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                    //$initialPayment += $res->getOwnResNightPrice() * $nights * $comission;
                } else {
                    $totalPrice += $ownReservation->getOwnResTotalInSite();
                    //$initialPayment += $res->getOwnResTotalInSite() * $comission;
                }

                $ownReservation = $ownReservations->next();
            } while ($ownReservation);
        }
        return array("price"=>$totalPrice,"booking"=>$booking);
    }
   public function InitializeLedger( $start, $limit, $draw,$account,$curr,$to){
       $filters=array("to_limit"=>$to);

       $em = $this->getDoctrine()->getManager();
       $reservations_reserved=$this->getReservationsData($filters,$start,$limit,$draw,generalReservation::STATUS_RESERVED);
//       $reservations_pending=$this->getReservationsData($filters,$start,$limit,$draw,generalReservation::STATUS_PENDING_PAYMENT_PARTNER);

       foreach ($reservations_reserved as $reservation){
           $price_booking=$this->getPriceandBooking($reservation,$curr);
           $ledge_temp=new paAccountLedgers();
           $ledge_temp->setAccount($account);
           $ledge_temp->setCreated($reservation->getGenResToDate());
           $ledge_temp->setDescription("Client:".$reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName().","."BR:".
               $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference().","."CAS-".$reservation->getGenResId().
           ","."Booking:".$price_booking['booking']);
           $balance= $ledge_temp->setCredit(round($price_booking['price']+($price_booking['price']*0.1)+(($price_booking['price']+($price_booking['price']*0.1))*0.1),2),$account->getBalance());
           $ledge_temp->setCas($reservation->getGenResId());
           $account->setBalance($balance);
           $em->persist($account);
           $em->persist($ledge_temp);
           $em->flush();

       }

//      foreach ($reservations_pending as $reservation){
//
//            $price_booking=$this->getPriceandBooking($reservation,$curr);
//            $ledge_temp=new paAccountLedgers();
//            $ledge_temp->setAccount($account);
//            $ledge_temp->setCas($reservation->getGenResId());
//            $ledge_temp->setCreated($reservation->getGenResToDate());
//            $ledge_temp->setDescription("Client:".$reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName().","."BR:".
//                $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference().","."CAS-".$reservation->getGenResId().
//                ","."Booking:".$price_booking['booking']);
//            $balance= $ledge_temp->setCredit(round($price_booking['price']+($price_booking['price']*0.1)+(($price_booking['price']+($price_booking['price']*0.1))*0.1),2),$account->getBalance());
//            $account->setBalance($balance);
//            $em->persist($account);
//            $em->persist($ledge_temp);
//            $em->flush();
//
//        }

        return true;
    }
   public function UpdateLedger( $start, $limit, $draw,$account,$curr,$last_ledger_cas,$from,$to){
        $em = $this->getDoctrine()->getManager();

        $filters = array("to_between" => array($last_ledger_cas, $from, $to));

        $reservations_reserved=$this->getReservationsData($filters,$start,$limit,$draw,generalReservation::STATUS_RESERVED);

        foreach ($reservations_reserved as $reservation){
            $price_booking=$this->getPriceandBooking($reservation,$curr);
            $ledge_temp=new paAccountLedgers();
            $ledge_temp->setAccount($account);
            $ledge_temp->setCas($reservation->getGenResId());

            $ledge_temp->setCreated($reservation->getGenResToDate());
            $ledge_temp->setDescription("Client:".$reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName().","."BR:".
                $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference().","."CAS-".$reservation->getGenResId().
                ","."Booking:".$price_booking['booking']);
            $balance= $ledge_temp->setCredit(round($price_booking['price']+($price_booking['price']*0.1)+(($price_booking['price']+($price_booking['price']*0.1))*0.1),2),$account->getBalance());

            $account->setBalance($balance);
            $em->persist($account);
            $em->persist($ledge_temp);
            $em->flush();

        }
//        $reservations_pending=$this->getReservationsData($filters,$start,$limit,$draw,generalReservation::STATUS_PENDING_PAYMENT_PARTNER);
//        foreach ($reservations_pending as $reservation){
//            $price_booking=$this->getPriceandBooking($reservation,$curr);
//            $ledge_temp=new paAccountLedgers();
//            $ledge_temp->setCas($reservation->getGenResId());
//            $ledge_temp->setAccount($account);
//            $ledge_temp->setCreated($reservation->getGenResToDate());
//            $ledge_temp->setDescription("Client:".$reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName().","."BR:".
//                $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference().","."CAS-".$reservation->getGenResId().
//                ","."Booking:".$price_booking['booking']);
//            $balance= $ledge_temp->setCredit(round($price_booking['price']+($price_booking['price']*0.1)+(($price_booking['price']+($price_booking['price']*0.1))*0.1),2),$account->getBalance());
//
//            $account->setBalance($balance);
//            $em->persist($account);
//            $em->persist($ledge_temp);
//            $em->flush();
//
//        }
        
        return true;
    }
   public function sumaryAccountingAction(Request $request)
    {
        $filters = $request->get('booking_reserved_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $ledgers_repo=$em->getRepository("PartnerBundle:paAccountLedgers");
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $agency = $tourOperator->getTravelAgency();
        $account =$agency->getAccount();

        $ledge=$account->getLedgers();

        #region PAGINADO
        $start = $request->get('start', 0);

        $limit = $request->get('length', 10);
        $draw = $request->get('draw') + 1;
        $curr=$this->getCurr($request);
        #endregion PAGINADO
        if($account->getBalance()>0) {

            if (count($ledge) == 0) {
                $today = date('d-m-Y');

                $this->InitializeLedger($start, $limit, $draw, $account, $curr, $today);

            }
            else {
                $last_created_date = $ledgers_repo->getLastLedger($account->getId())->getCreated();

                $last_ledger_cas = $ledgers_repo->getLastLedgerCas($account->getId());
                $cas=null;
                if($last_ledger_cas!=null)    {
                   $cas= $last_ledger_cas->getCas();
                }

               
                $today = date('d-m-Y');
                if($cas==null&& $last_created_date==date('d-m-Y')){

                }
                else {
                    $this->UpdateLedger($start, $limit, $draw, $account, $curr, $cas, $last_created_date, $today);
                }
            }
            $today = date('d-m-Y');
            $today = date('d-m-Y', strtotime($today . ' +1 day'));
            $first = date('01-m-Y');

            $dates = array();
            $to = (array_key_exists('to', $filters) && isset($filters['to']));
            $from = (array_key_exists('from', $filters) && isset($filters['from']));

            if ($from) {
                if($filters['from']!='')
                $first = $filters['from'];
            }
            if ($to) {
                if($filters['to']!='')
                $today = $filters['to'];
            }

            $dates = array("to_between" => array($first, $today));
            $data = $ledgers_repo->getallLedger($account->getId(), $dates);

            $count=0;
            $ledgers=array();
            if(count($data)>0) {
                if( $data[0]['credit']>0) {
                    $ledgers = array(array('no' => 1,
                        'description' => 'Estado de la cuenta al inicio del día ' . $data[0]['created']->format('d-m-Y'),
                        "created" => $data[0]['created']->format('d-m-Y'),
                        'credit' => '',
                        'debit' => round(($data[0]['balance'] + $data[0]['credit']) * $curr['change'], 2),
                        'balance' => round(($data[0]['balance'] + $data[0]['credit']) * $curr['change'], 2))
                    );
                    $count = 1;
                }
                else{
                    $count = 0;
                }

            }
            foreach ($data as $ledge) {

                $arrTmp = array();
                $count += 1;
                $arrTmp['no'] = $count;
                $arrTmp['description'] = $ledge['description'];
                $arrTmp['created'] = $ledge['created']->format('d-m-Y');
                $arrTmp['credit'] =($ledge['credit'] != 0) ? round($ledge['credit'] * $curr['change'],2): '' ;
                $arrTmp['debit'] = ($ledge['debit'] != 0) ? round($ledge['debit'] * $curr['change'],2): '';
                $arrTmp['balance'] = round($ledge['balance'] * $curr['change'] ,2);
                $arrTmp['cas'] = $ledge['cas'] ;

                array_push($ledgers, $arrTmp);
            }

            return new JsonResponse($ledgers);
        }
        else{
            return new JsonResponse(array('message'=>'Para comensar operaciones añada un primer deposito'));
        }
    }
   public function getCurr(Request $request)
    {
        $session = $request->getSession();
        $a = $session->get("curr_rate");
        $b = $session->get("curr_symbol");
        $c = $session->get("curr_acronym");

        return array("change" => $a, "code" => $c);
    }
   public function addDepositAction( Request $request){
       try{

           $user = $this->getUser();
           $curr=$this->getCurr($request);
           $em = $this->getDoctrine()->getManager();
           $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
           $agency = $tourOperator->getTravelAgency();
           $account =$agency->getAccount();
           $date=$request->get('date');
           $desc=$request->get('description');
           $cash=$request->get('cash')/$curr['change'];
           $obj=new paAccountLedgers();
           $obj->setAccount($account);

           $balance=$obj->setDebit($cash,$account->getBalance());
           $account->setBalance($balance);
           $desc=$desc.'-'. $this->get('translator')->trans('label.accounting.registerby').$user->getUsername().','.date('d-m-Y').','.date('h:i-A').'';

          $obj->setDescription($desc);
          $obj->setCreated(new \DateTime(date('d-m-Y')));
          $em->persist($obj);
          $em->persist($account);
          $em->flush();
           $service_email = $this->get('Email');
           $emailBody = $this->renderView('FrontEndBundle:mails:rt_agency_deposit.html.twig', array(
               'travelAgency' => $agency,
               'deposit' => $cash,

           ));
           $service_email->sendEmail(
               "Nuevo Deposito", 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', 'contabilidad@mycasaparticular.com', $emailBody
           );
           $service_email->sendEmail(
               "Nuevo Deposito", 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', 'contabilidad1@mycasaparticular.com', $emailBody
           );
           $service_email->sendEmail(
               "Nuevo Deposito", 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', 'laura@hds.li', $emailBody
           );
           $service_email->sendEmail(
               "Nuevo Deposito", 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', 'orlando@hds.li', $emailBody
           );
           return new JsonResponse(array('success'=>true,'message'=>'Para comensar operaciones añada un primer deposito'));

       }
       catch (Exception $a){
           return new JsonResponse(array('success'=>false,'message'=>'Para comensar operaciones añada un primer deposito'));

       }



        }
   public function sendConciliationAction( Request $request){
        try{
            $today = date('d-m-Y');
            $today = date('d-m-Y', strtotime($today . ' +1 day'));
            $first = date('01-m-Y');
            $user = $this->getUser();
            $curr=$this->getCurr($request);
            $em = $this->getDoctrine()->getManager();
            $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
            $agency = $tourOperator->getTravelAgency();
            $account =$agency->getAccount();

            $debit=$request->get('debit');
            $credit=$request->get('credit');
            $balance=$request->get('balance');
            $from=$request->get('from');
            $to=$request->get('to');
            $obj=new paAccountLedgers();
            $obj->setAccount($account);
            if ($from=='') {

              $from = $first;
            }
            if ($to=='') {
                $to=$today;
            }

            $service_email = $this->get('Email');
            $emailBody = $this->renderView('FrontEndBundle:mails:rt_agency_conciliation.html.twig', array(
                'travelAgency' => $agency,
                'debit' => $debit,
                'credit'=>$credit,
                'balance'=>$balance,
                'from'=>$from,
                'to'=>$to

            ));
            $service_email->sendEmail(
                "Conciliacion", 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', 'contabilidad@mycasaparticular.com', $emailBody
            );
            $service_email->sendEmail(
                "Conciliacion", 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', 'contabilidad1@mycasaparticular.com', $emailBody
            );
            $service_email->sendEmail(
                "Conciliacion", 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', 'laura@hds.li', $emailBody
            );


            return new JsonResponse(array('success'=>true,'message'=>'Para comensar operaciones añada un primer deposito'));

        }
        catch (Exception $a){
            return new JsonResponse(array('success'=>false,'message'=>'Para comensar operaciones añada un primer deposito'));

        }



    }

}
