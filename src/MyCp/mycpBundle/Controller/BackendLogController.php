<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\FileIO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class BackendLogController extends Controller
{
    function list_logsAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $logger= $this->get('log');
        $service_security->verifyAccess();
        $logs=array();
        $msg_count_logs=0;
        $msg_session=0;
        $post = $request->request->getIterator()->getArrayCopy();
        $logsFiles = $logger->getLogFiles();
        if($request->getMethod()=='POST')
        {
            $post['submit']=1;
            if($post['user']!='' || $post['module']!='' || $post['from_date']!='' || $post['to_date']!='' || $post['role'] != "")
            {
                $em = $this->getDoctrine()->getManager();
                $logs=$em->getRepository('mycpBundle:log')->getLogs($post);
                $msg_count_logs=count($logs);
            }
            else{
                $message='Por favor, seleccione un criterio para filtrar.';
                $this->get('session')->getFlashBag()->add('message_error_local',$message);
            }
        }

        return $this->render('mycpBundle:log:list.html.twig',array('post'=>$post,'logs'=>$logs,'count_logs'=>$msg_count_logs,'msg_session'=>$msg_session, 'logFiles' => $logsFiles));
    }

    function get_rolesAction($selected,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $roles=$em->getRepository('mycpBundle:role')->findAll();
        if(isset($selected['role']))
            $selected=$selected['role'];
        return $this->render('mycpBundle:utils:roles.html.twig',array('roles'=>$roles,'selected'=>$selected));
    }

    function get_modulesAction($selected,Request $request)
    {
        $selected= (isset($selected['module']) && $selected['module'] != null && $selected['module'] != "") ? $selected['module'] : "-1";
        return $this->render('mycpBundle:utils:modules.html.twig',array('selected'=>$selected));
    }

    function get_usersAction($selected,$role,Request $request)
    {

        $em = $this->getDoctrine()->getManager();
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
        $em=$this->getDoctrine()->getManager();
        $logs=$em->getRepository('mycpBundle:log')->findAll();
        foreach($logs as $log)
        {
            $em->remove($log);
        }
        $em->flush();

        $message='Se han limpiado los logs satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok',$message);

        return $this->redirect($this->generateUrl('mycp_list_logs'));
    }

    function downloadFileAction($fileName){
        $logger= $this->get('log');
        $filePath = $logger->getFilesPath();

        return FileIO::download($filePath, $fileName);
    }
}
