<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class BackendLogController extends Controller
{
    function list_logsAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $logs=array();
        $msg_count_logs=0;
        $msg_session=0;
        $post = $request->request->getIterator()->getArrayCopy();
        if($request->getMethod()=='POST')
        {
            $post['submit']=1;
            if(!isset($post['user']) OR isset($post['user']) && $post['user']=='')
            {
                $message='Debe seleccionar un usuario.';
                $this->get('session')->getFlashBag()->add('message_error_local',$message);
                $msg_session=1;
            }
            else
            if($post['user']!='' or $post['module']!='' or $post['from_date']!='' or $post['to_date']!='')
            {
                $em = $this->getDoctrine()->getEntityManager();
                $logs=$em->getRepository('mycpBundle:log')->get_logs($post);
                $msg_count_logs=count($logs);
            }


        }
        if(!isset($post['role']))
            $post['role']='';
        return $this->render('mycpBundle:log:list.html.twig',array('post'=>$post,'logs'=>$logs,'count_logs'=>$msg_count_logs,'msg_session'=>$msg_session));
    }

    function get_rolesAction($selected,Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $roles=$em->getRepository('mycpBundle:role')->findAll();
        if(isset($selected['role']))
            $selected=$selected['role'];
        return $this->render('mycpBundle:utils:roles.html.twig',array('roles'=>$roles,'selected'=>$selected));
    }

    function get_modulesAction($selected,Request $request)
    {
        if(isset($selected['module']))
            $selected=$selected['module'];
        return $this->render('mycpBundle:utils:modules.html.twig',array('selected'=>$selected));
    }

    function get_usersAction($selected,$role,Request $request)
    {

        $em = $this->getDoctrine()->getEntityManager();
        $params=array();
        if($role!='')
        {
            $role_db=$em->getRepository('mycpBundle:role')->findBy(array('role_name'=>$role));
            $params=array('user_subrole'=>$role_db[0]->getRoleId());
        }
        $users=$em->getRepository('mycpBundle:user')->findBy($params);
        if(isset($selected['user']))
            $selected=$selected['user'];
        return $this->render('mycpBundle:utils:users.html.twig',array('selected'=>$selected,'users'=>$users));
    }

    function delete_logsAction()
    {
        $em=$this->getDoctrine()->getEntityManager();
        $logs=$em->getRepository('mycpBundle:log')->findAll();
        foreach($logs as $log)
        {
            $em->remove($log);
        }
        $em->flush();

        $message='Se han limpiado los logs satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok',$message);

        /*$service_log= $this->get('log');
        $service_log->saveLog('Create category, '.$post['lang'.$languages[0]->getLangId()],3);*/

        return $this->redirect($this->generateUrl('mycp_list_logs'));
    }
}
