<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\log;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Form\reservationType;



class BackendReservationController extends Controller
{

    public function list_reservationsAction($items_per_page, Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $page=1;
        $filter_date_reserve=$request->get('filter_date_reserve');
        $filter_offer_number=$request->get('filter_offer_number');
        $filter_reference=$request->get('filter_reference');
        $filter_date_from=$request->get('filter_date_from');
        $filter_date_to=$request->get('filter_date_to');
        if($request->getMethod()=='POST' && $filter_date_reserve=='null' && $filter_offer_number=='null' && $filter_reference=='null'&&
            $filter_date_from=='null' && $filter_date_to=='null'
        )
        {
            $message='Debe llenar al menos un campo para filtrar.';
            $this->get('session')->setFlash('message_error_local',$message);
            return $this->redirect($this->generateUrl('mycp_list_reservations'));
        }
        if($filter_date_reserve=='null') $filter_date_reserve='';
        if($filter_offer_number=='null') $filter_offer_number='';
        if($filter_reference=='null') $filter_reference='';
        if($filter_date_from=='null') $filter_date_from='';
        if($filter_date_to=='null') $filter_date_to='';
        if(isset($_GET['page']))$page=$_GET['page'];
        $filter_date_reserve=str_replace('_','/',$filter_date_reserve);
        $filter_date_from=str_replace('_','/',$filter_date_from);
        $filter_date_to=str_replace('_','/',$filter_date_to);

        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $reservations= $paginator->paginate($em->getRepository('mycpBundle:ownershipReservation')
            ->get_all_reservations($filter_date_reserve, $filter_offer_number, $filter_reference,
            $filter_date_from,$filter_date_to))->getResult();

        $filter_date_reserve_twig=str_replace('/','_',$filter_date_reserve);
        $filter_date_from_twig=str_replace('/','_',$filter_date_from);
        $filter_date_to_twig=str_replace('/','_',$filter_date_to);

        $service_log= $this->get('log');
        $service_log->save_log('Visit',7);

        return $this->render('mycpBundle:reservation:list.html.twig',array(
            'reservations'=>$reservations,
            'items_per_page'=>$items_per_page,
            'current_page'=>$page,
            'total_items'=>$paginator->getTotalItems(),
            'filter_date_reserve'=>$filter_date_reserve,
            'filter_offer_number'=>$filter_offer_number,
            'filter_reference'=>$filter_reference,
            'filter_date_from'=>$filter_date_from,
            'filter_date_to'=>$filter_date_to,
            'filter_date_reserve_twig'=>$filter_date_reserve_twig,
            'filter_date_from_twig'=>$filter_date_from_twig,
            'filter_date_to_twig'=>$filter_date_to_twig
        ));
    }

    public function new_reservationAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $data=array();
        $em = $this->getDoctrine()->getEntityManager();
        $role=$em->getRepository('mycpBundle:role')->findBy(array('role_name'=>'ROLE_CLIENT_TOURIST'));
        $post=$request->get('mycp_mycpbundle_reservationtype');
        $ownerships= $em->getRepository('mycpBundle:ownership')->findAll();
        $rooms=$em->getRepository('mycpBundle:room')->findBy(array('room_ownership'=>$post['reservation_ownership']));
        $users=$em->getRepository('mycpBundle:user')->findAll();
        $data['ownerships']=$ownerships;
        $data['rooms']=$rooms;
        $data['users']=$users;

        $form = $this->createForm(new reservationType($data));

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $em->getRepository('mycpBundle:ownershipReservation')->new_reservation($post);
                $message='Reserva añadida satisfactoriamente.';
                $ownership= $em->getRepository('mycpBundle:ownership')->find($post['reservation_ownership']);

                $service_log= $this->get('log');
                $service_log->save_log('Create entity for '.$ownership->getOwnMcpCode(),7);

                $this->get('session')->setFlash('message_ok',$message);
                return $this->redirect($this->generateUrl('mycp_list_reservations'));
            }
        }
        return $this->render('mycpBundle:reservation:new.html.twig',array('form'=>$form->createView(),'role'=>$role[0]));
    }

    public function edit_reservationAction($id_reservation,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $em = $this->getDoctrine()->getEntityManager();
        $reservation=$em->getRepository('mycpBundle:ownershipReservation')->get_reservation_by_id($id_reservation);

        $user_id=$reservation[0]['own_res_gen_res_id']['gen_res_user_id']['user_id'];
        $reservations_user=$em->getRepository('mycpBundle:ownershipReservation')->get_reservations_by_id_user($user_id);

        $data['total']=0;
        $data['post']=0;
        $errors=array();
        foreach($reservations_user as $reser)
        {

            $dif = $reser['own_res_reservation_from_date']->diff($reser['own_res_reservation_to_date']);
            $dif= $dif->format('%r%a');
            $data['total']+= $reser['own_res_night_price']*($dif-1);
        }
        $currency_change=$reservation[0]['own_res_gen_res_id']['gen_res_user_id']['user_currency']['curr_cuc_change'];
        $data['total_cuc']=$data['total']/$currency_change;
        $service_cost=$em->getRepository('mycpBundle:config')->findAll();
        $data['service_cost_cuc']=$service_cost[0]->getConfServiceCost();
        $data['service_cost']=$data['service_cost_cuc']*$currency_change;

        $data['total_neto_cuc']=$data['total_cuc']+$data['service_cost_cuc'];
        $data['total_neto']=$data['total_neto_cuc']*$currency_change;

        $data['commission_percent_cuc']=$data['total_cuc']*$reservation[0]['own_res_commission_percent']/100;
        $data['commission_percent']=$data['commission_percent_cuc']*$currency_change;

        $data['avance_total_cuc']=$data['service_cost_cuc']+$data['commission_percent_cuc'];
        $data['avance_total']=$data['avance_total_cuc']*$currency_change;

        $data['pay_cuba_cuc']=$data['total_cuc']-$data['avance_total_cuc'];
        $data['pay_cuba']=$data['pay_cuba_cuc']*$currency_change;


        $post = $request->request->getIterator()->getArrayCopy();
        $post['selected_room']=$reservation[0]['own_res_selected_room'];
        if($request->getMethod()=='POST')
        {
            $data['ownership']= $request->get('ownership');
            $post['selected_room']=$request->get('selected_room');
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message="Este campo no puede estar vacío.";
            $array_keys=array_keys($post);
            $count=$errors_validation=$count_errors= 0;
            foreach ($post as $item) {
                $errors[$array_keys[$count]] = $errors_validation=$this->get('validator')->validateValue($item, $not_blank_validator);
                if($array_keys[$count]!='percent')
                    $count_errors+=count($errors_validation);
                $count++;
            }

            if ($count_errors == 0) {
                $em->getRepository('mycpBundle:ownershipReservation')->edit_reservation($reservation[0],$post);
                $message='Reserva actualizada satisfactoriamente.';
                $ownership= $em->getRepository('mycpBundle:ownership')->find($post['ownership']);

                $service_log= $this->get('log');
                $service_log->save_log('Edit entity for '.$ownership->getOwnMcpCode(),7);

                $this->get('session')->setFlash('message_ok',$message);
                return $this->redirect($this->generateUrl('mycp_edit_reservation',array('id_reservation'=>$id_reservation)));
            }
        }
        else{
            $data['ownership']= $reservation[0]['own_res_own_id']['own_id'];
            $post['percent']=$reservation[0]['own_res_commission_percent'];
        }

        $reservation=$reservation[0];
        return $this->render('mycpBundle:reservation:reservationEdit.html.twig',array('errors'=>$errors,'data'=>$data,'reservation'=>$reservation,'id_reservation'=>$id_reservation,'post'=>$post));
    }

    public function get_ownershipsAction($data)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $ownerships=$em->getRepository('mycpBundle:ownership')->findAll();
        return $this->render('mycpBundle:utils:ownerships.html.twig',array('ownerships'=>$ownerships,'data'=>$data));

    }

    public function get_percent_listAction($post)
    {
        $selected='';
        if(isset($post['percent']))
            $selected=$post['percent'];
        return $this->render('mycpBundle:utils:percent.html.twig',array('selected'=>$selected));
    }

    public function get_rooms_by_ownershipAction($id_ownership,$selected_room)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $rooms=$em->getRepository('mycpBundle:room')->findBy(array('room_ownership'=>$id_ownership));
        return $this->render('mycpBundle:utils:rooms.html.twig',array('rooms'=>$rooms,'selected_room'=>$selected_room));
    }

    public function delete_reservationAction($id_reservation)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $reservation=$em->getRepository('mycpBundle:ownershipReservation')->find($id_reservation);
        $ownership= $em->getRepository('mycpBundle:ownership')->find($reservation->getOwnResOwnId());
        $em->remove($reservation->getOwnResGenResId());
        $em->remove($reservation);
        $em->flush();
        $message='Reserva eliminada satisfactoriamente.';

        $service_log= $this->get('log');
        $service_log->save_log('Delete entity for '.$ownership->getOwnMcpCode(),7);

        $this->get('session')->setFlash('message_ok',$message);
        return $this->redirect($this->generateUrl('mycp_list_reservations'));

    }
}
