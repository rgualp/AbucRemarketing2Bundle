<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\province;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BackendProvinceController extends Controller
{

    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $provinces = $em->getRepository('mycpBundle:province')->findAll();
        return $this->render('mycpBundle:province:list.html.twig', array(
            'provinces' => $provinces,
        ));

    }

    public function changeStateAction(Request $request, province $province, $state)
    {
        $em = $this->getDoctrine()->getManager();
        $province->setEnabled($state);
        $em->flush();
        $message = 'Provincia deshabilitada correctamente.';
        if ($state)
            $message = 'Provincia habilitada correctamente.';
        $this->addFlash(
            'message_ok', $message
        );
        return $this->redirectToRoute('mycp_province_list');

    }

}
