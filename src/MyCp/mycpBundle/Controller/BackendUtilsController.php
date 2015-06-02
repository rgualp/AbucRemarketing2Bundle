<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class BackendUtilsController extends Controller
{

    public function set_img_orderAction($ids,Request $request)
    {
        $ids=str_replace(' ','',$ids);
        $ids_array= explode(",", $ids);
        $em = $this->getDoctrine()->getEntityManager();
        $order=1;
        foreach($ids_array as $id)
        {
            $photo=$em->getRepository('mycpBundle:photo')->find($id);
            $photo->setPhoOrder($order);
            $em->persist($photo);
            $em->flush();
            $order++;
        }
        return new Response($ids);
    }

    public function get_active_listAction($selected)
    {
        return $this->render('mycpBundle:utils:active.html.twig',array('selected'=>$selected));
    }

    function orderByAction($sortBy, $elementToOrder, $doTranlations = false) {
        $selected = '';
        if (isset($sortBy))
            $selected = $sortBy;
        $orderItems = \MyCp\mycpBundle\Helpers\OrderByHelper::getOrdersFor($elementToOrder);
        return $this->render('mycpBundle:utils:orderBy.html.twig', array('selected' => $selected, "orderItems" => $orderItems, "translate" => $doTranlations));
    }


}
