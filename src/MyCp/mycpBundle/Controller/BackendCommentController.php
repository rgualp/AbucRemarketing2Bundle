<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\DataBaseTables;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Entity\comment;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Form\commentType;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendCommentController extends Controller {

    public function list_commentAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $page = 1;
        $data = '';
        $filter_ownership = $request->get('filter_ownership');
        $filter_user = $request->get('filter_user');
        $sort_by = $request->get('sort_by');
        $filter_keyword = $request->get('filter_keyword');
        $filter_rate = $request->get('filter_rate');
        if ($request->getMethod() == 'POST' && $filter_ownership == 'null' && $filter_user == 'null' && $filter_keyword == 'null' && $filter_rate == 'null' && $sort_by == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_comments'));
        }
        if ($filter_ownership == 'null')
            $filter_ownership = '';
        if ($filter_keyword == 'null')
            $filter_keyword = '';
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $em = $this->getDoctrine()->getManager();
        $comments = $paginator->paginate($em->getRepository('mycpBundle:comment')->getAll($filter_ownership, $filter_user, $filter_keyword, $filter_rate, $sort_by))->getResult();
        //var_dump($destinations[0]->getDesLocMunicipality()->getMunName()); exit();

//        $service_log = $this->get('log');
//        $service_log->saveLog('Visit module', BackendModuleName::MODULE_COMMENT);
        return $this->render('mycpBundle:comment:list.html.twig', array(
                    'comments' => $comments,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
                    'filter_ownership' => $filter_ownership,
                    'filter_user' => $filter_user,
                    'filter_keyword' => $filter_keyword,
                    'filter_rate' => $filter_rate,
                    'sort_by' => $sort_by,
                    'return_url' => 'mycp_list_comments'
        ));
    }

    public function new_commentAction(Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $comment = new comment();
        $form = $this->createForm(new commentType(), $comment);
        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_currencytype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $comment->setComDate(new \DateTime(date('Y-m-d')));
                $em->persist($comment);
                $em->flush();

                $em->getRepository("mycpBundle:ownership")->updateRating($comment->getComOwnership());

                if($comment->getComOwnership()->getOwnEmail1()!=null) {
                    $body = $this->render('FrontEndBundle:mails:commentNotification.html.twig', array(
                        'host_user_name' => $comment->getComOwnership()->getOwnHomeowner1(),
                        'user_name' => $comment->getComUser()->getName() . ' ' . $comment->getComUser()->getUserLastName(),
                        'comment' => $comment->getComComments()
                    ));

                    $service_email = $this->get('mycp.service.email_manager');
                    $service_email->sendEmail($comment->getComOwnership()->getOwnEmail1(),'Nuevos comentarios recibidos', $body->getContent());
                }
                $message = 'Comentario añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog($comment->getLogDescription(), BackendModuleName::MODULE_COMMENT, log::OPERATION_INSERT, DataBaseTables::COMMENT);

                return $this->redirect($this->generateUrl('mycp_list_comments'));
            }
        }
        return $this->render('mycpBundle:comment:new.html.twig', array('form' => $form->createView()));
    }

    public function edit_commentAction($id_comment, $return_url, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $message = '';
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('mycpBundle:comment')->find($id_comment);
        $form = $this->createForm(new commentType, $comment);
        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_commenttype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($comment);
                $em->flush();
                $ownership = $comment->getComOwnership();
                $em->getRepository("mycpBundle:ownership")->updateRanking($ownership);
                $em->getRepository("mycpBundle:ownership")->updateRating($ownership);
                $message = 'Comentario actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog($comment->getLogDescription(), BackendModuleName::MODULE_COMMENT, log::OPERATION_UPDATE, DataBaseTables::COMMENT);

                if ($return_url == '' || $return_url == 'null' || $return_url == null)
                    return $this->redirect($this->generateUrl('mycp_list_comments'));
                else
                    return $this->redirect($this->generateUrl($return_url));
            }
        }

        return $this->render('mycpBundle:comment:new.html.twig', array('form' => $form->createView(), 'edit_comment' => $comment->getComId()));
    }

    public function delete_commentAction($id_comment, $return_url) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('mycpBundle:comment')->find($id_comment);

//        $user_comment = $comment->getComUser()->getName();
//        $own_comment = $comment->getComOwnership()->getOwnMcpCode();
        $em->remove($comment);
        $em->flush();

        $em->getRepository("mycpBundle:ownership")->updateRanking($comment->getComOwnership());
        $em->getRepository("mycpBundle:ownership")->updateRating($comment->getComOwnership());

        $message = 'El comentario se ha eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog($comment->getLogDescription(), BackendModuleName::MODULE_COMMENT, log::OPERATION_DELETE, DataBaseTables::COMMENT);

        if ($return_url == '' || $return_url == 'null' || $return_url == null)
            return $this->redirect($this->generateUrl('mycp_list_comments'));
        else
            return $this->redirect($this->generateUrl($return_url));
    }

    public function get_comments_by_ownershipAction($id_own) {
        $em = $this->getDoctrine()->getManager();
        $comments = $em->getRepository('mycpBundle:comment')->findBy(array('com_ownership' => $id_own), array('com_public' => 'ASC', 'com_date' => "DESC"));
        return $this->render('mycpBundle:utils:comments.html.twig', array('comments' => $comments));
    }

    public function get_all_rateAction($post) {
        $selected = '';
        if (isset($post['selected']))
            $selected = $post['selected'];
        return $this->render('mycpBundle:utils:range_max_5_no_0.html.twig', array('selected' => $selected));
    }

    public function publicAction($id_comment, $return_url, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('mycpBundle:comment')->find($id_comment);

        $comment->setComPublic(true);
        $em->persist($comment);
        $em->flush();

        $ownership = $comment->getComOwnership();
        $em->getRepository("mycpBundle:ownership")->updateRanking($ownership);
        $em->getRepository("mycpBundle:ownership")->updateRating($ownership);
        $message = 'Comentario publicado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog($comment->getLogDescription()." (Publicado)", BackendModuleName::MODULE_COMMENT, log::OPERATION_UPDATE, DataBaseTables::COMMENT);

        if ($return_url == '' || $return_url == 'null' || $return_url == null)
            return $this->redirect($this->generateUrl('mycp_list_comments'));
        else
            return $this->redirect($this->generateUrl($return_url));
    }

    public function lastAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $page = 1;
        $data = '';
        $filter_ownership = $request->get('filter_ownership');
        $filter_user = $request->get('filter_user');
        $sort_by = $request->get('sort_by');
        $filter_keyword = $request->get('filter_keyword');
        $filter_rate = $request->get('filter_rate');
        if ($request->getMethod() == 'POST' && $filter_ownership == 'null' && $filter_user == 'null' && $filter_keyword == 'null' && $filter_rate == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_last_comments'));
        }
        if ($filter_ownership == 'null')
            $filter_ownership = '';
        if ($filter_keyword == 'null')
            $filter_keyword = '';
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $em = $this->getDoctrine()->getManager();
        $comments = $paginator->paginate($em->getRepository('mycpBundle:comment')->getLastAdded($filter_ownership, $filter_user, $filter_keyword, $filter_rate, $sort_by))->getResult();
        //var_dump($destinations[0]->getDesLocMunicipality()->getMunName()); exit();

//        $service_log = $this->get('log');
//        $service_log->saveLog('Visit module', BackendModuleName::MODULE_COMMENT);
        return $this->render('mycpBundle:comment:list.html.twig', array(
                    'comments' => $comments,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
                    'filter_ownership' => $filter_ownership,
                    'filter_user' => $filter_user,
                    'filter_keyword' => $filter_keyword,
                    'filter_rate' => $filter_rate,
                    'sort_by' => $sort_by,
                    'last_added' => true,
                    'return_url' => 'mycp_last_comment'
        ));
    }

    public function publicSelectedCallbackAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $ids = $request->request->get('comments_ids');
        $returnUrl = $request->request->get('return_url');
        $response = "OK";

        try {
            //Publicar comentarios
            $em->getRepository('mycpBundle:comment')->publicMultiples($ids);

            $message = 'Se han publicado ' . count($ids) . ' comentarios.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            $service_log = $this->get('log');
            $service_log->saveLog("Publicados ".count($ids).' comentarios', BackendModuleName::MODULE_COMMENT, log::OPERATION_UPDATE, DataBaseTables::COMMENT);

            $response = $this->generateUrl($returnUrl);
            //return $this->redirect($this->generateUrl($returnUrl));
        } catch (\Exception $e) {
            $message = 'Ha ocurrido un error durante la publicación de los comentarios.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            $response = "ERROR";
        }
        return new Response($response);
    }

    public function deleteSelectedCallbackAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $ids = $request->request->get('comments_ids');
        $returnUrl = $request->request->get('return_url');
        $response = "OK";

        try {
            //Publicar comentarios
            $em->getRepository('mycpBundle:comment')->deleteMultiples($ids);

            $message = 'Se han eliminado ' . count($ids) . ' comentarios satisfactoriamente.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            $service_log = $this->get('log');
            $service_log->saveLog("Eliminados ".count($ids).' comentarios', BackendModuleName::MODULE_COMMENT, log::OPERATION_DELETE, DataBaseTables::COMMENT);

            $response = $this->generateUrl($returnUrl);
            //return $this->redirect($this->generateUrl($returnUrl));
        } catch (\Exception $e) {
            $message = 'Ha ocurrido un error durante la eliminación de los comentarios.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            $response = "ERROR";
        }
        return new Response($response);
    }

    public function changePositiveCallbackAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $positive = $request->get("positive");
        $id = $request->get("idComment");
        $comment = $em->getRepository('mycpBundle:comment')->find($id);

        $comment->setPositive(($positive == "1" || $positive == "true") ? 1 : 0);
        $em->persist($comment);
        $em->flush();

        $isFromUserWithReservation = $em->getRepository('mycpBundle:comment')->isFromUserWithReservations($comment);

        $commentContent =  $this->renderView('mycpBundle:comment:positiveComment.html.twig', array(
            'comment' => $comment,
            "isFromUserWithReservation" => $isFromUserWithReservation
        ));

        return new Response($commentContent, 200);
    }
}
