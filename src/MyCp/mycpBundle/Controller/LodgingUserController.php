<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\BackendModuleName;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class LodgingUserController extends Controller
{
    function edit_profileAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $user = $this->get('security.context')->getToken()->getUser();
        $id_user = $user->getUserId();
        return $this->render('mycpBundle:user:user_casa_short_form.html.twig',array('edit_user'=>$id_user));
    }

    function update_profileAction($id_user, Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();

        $data = array();

        //Validating...
        $pass = $request->get('user_password');
        $repeat = $request->get('user_repeat_password');

        if (strcmp($pass, $repeat) != 0)
            $data['error'] = 'Las claves deben coincidir';
        elseif (strlen($pass) > 1 && strlen($pass) < 6)
            $data['error'] = 'La longitud de la clave tiene que ser superior a 6 caracteres';


        if (isset ($data['error']))
            return $this->render('mycpBundle:user:user_casa_short_form.html.twig',array('edit_user'=>$id_user, 'data'=>$data));

        $em = $this->getDoctrine()->getEntityManager();
        $factory = $this->get('security.encoder_factory');

        $em->getRepository('mycpBundle:user')->shortEdit($id_user,$request,$this->container,$factory);
        $user_name = $em->getRepository('mycpBundle:user')->findOneBy(array('user_id' => $id_user))->getUserName();
        $message='Usuario actualizado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok',$message);
        $service_log= $this->get('log');
        $service_log->saveLog('Edit entity for user '.$user_name, BackendModuleName::MODULE_LODGING_USER);

        return $this->redirect($this->generateUrl('mycp_lodging_front'));

    }

}
