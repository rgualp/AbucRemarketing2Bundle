<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\comment;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Form\commentType;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BackendCommentController extends Controller
{

    const MESSAGE_ERROR_TYPE = 'message_error_main';

    public function list_commentAction($items_per_page, Request $request)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $page = 1;
        $data = '';
        $filter_ownership = $request->get('filter_ownership');
        $filter_user = $request->get('filter_user');
        $sort_by = $request->get('sort_by');
        $filter_keyword = $request->get('filter_keyword');
        $filter_rate = $request->get('filter_rate');
        $filter_date_from = $request->get('filter_date_from');
        $filter_date_to = $request->get('filter_date_to');
        if ($request->getMethod() == 'POST' && $filter_date_from == 'null' && $filter_date_to == 'null' && $filter_ownership == 'null' && $filter_user == 'null' && $filter_keyword == 'null' && $filter_rate == 'null' && $sort_by == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_comments'));
        }
        if ($filter_ownership == 'null')
            $filter_ownership = '';
        if ($filter_keyword == 'null')
            $filter_keyword = '';
        if ($filter_date_from == 'null')
            $filter_date_from = '';
        if ($filter_date_to == 'null')
            $filter_date_to = '';

        if (isset($_GET['page']))
            $page = $_GET['page'];
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $em = $this->getDoctrine()->getManager();
        $comments = $paginator->paginate($em->getRepository('mycpBundle:comment')->getAll($filter_ownership, $filter_user, $filter_keyword, $filter_rate, $sort_by, $filter_date_from, $filter_date_to))->getResult();

        return $this->render('mycpBundle:comment:list.html.twig', array(
            'comments' => $comments,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems(),
            'filter_ownership' => $filter_ownership,
            'filter_user' => $filter_user,
            'filter_keyword' => $filter_keyword,
            'filter_rate' => $filter_rate,
            'filter_date_from' => $filter_date_from,
            'filter_date_to' => $filter_date_to,
            'sort_by' => $sort_by,
            'return_url' => 'mycp_list_comments'
        ));
    }

    public function new_commentAction(Request $request)
    {
        $VIEW_COMMENT_NEW = 'mycpBundle:comment:new.html.twig';
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $comment = new comment();
        $form = $this->createForm(new commentType(), $comment);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $accommodation = $em->getRepository("mycpBundle:ownership")->findOneBy(array("own_mcp_code" => $comment->getComOwnershipCode()));
                $user = $em->getRepository("mycpBundle:user")->findOneBy(array("user_email" => $comment->getComUserEmail()));
                if (!is_null($user) && !is_null($accommodation)) {
                    $userTourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array("user_tourist_user" => $user->getUserId()));
                    if ($userTourist != null) {
                        $comment->setComDate(new \DateTime(date('Y-m-d')));
                        $comment->setComOwnership($accommodation);
                        $comment->setComUser($user);
                        $comment->setComByClient(true);
                        $em->persist($comment);
                        $em->flush();

                        $em->getRepository("mycpBundle:ownership")->updateRating($comment->getComOwnership());

                        if ($comment->getComOwnership()->getOwnEmail1() != null) {
                            $body = $this->render('FrontEndBundle:mails:commentNotification.html.twig', array(
                                'host_user_name' => $comment->getComOwnership()->getOwnHomeowner1(),
                                'user_name' => $comment->getComUser()->getName() . ' ' . $comment->getComUser()->getUserLastName(),
                                'comment' => $comment->getComComments()
                            ));

                            $service_email = $this->get('mycp.service.email_manager');
                            $service_email->sendEmail($comment->getComOwnership()->getOwnEmail1(), 'Nuevos comentarios recibidos', $body->getContent());
                        }
                        $message = 'Comentario añadido satisfactoriamente.';
                        $this->get('session')->getFlashBag()->add('message_ok', $message);

                        $service_log = $this->get('log');
                        $service_log->saveLog($comment->getLogDescription(), BackendModuleName::MODULE_COMMENT, log::OPERATION_INSERT, DataBaseTables::COMMENT);
                        return $this->redirect($this->generateUrl('mycp_list_comments'));

                    } else {
                        $message = 'No existe usuario asociado con ese correo';
                        $this->get('session')->getFlashBag()->add(self::MESSAGE_ERROR_TYPE, $message);
                        return $this->render($VIEW_COMMENT_NEW, array('form' => $form->createView()));
                    }
                } else {
                    $message = 'No existe usuario asociado con ese correo o no existe alojamiento con ese código';
                    $this->get('session')->getFlashBag()->add(self::MESSAGE_ERROR_TYPE, $message);
                    return $this->render($VIEW_COMMENT_NEW, array('form' => $form->createView()));
                }

            }
        }
        return $this->render($VIEW_COMMENT_NEW, array('form' => $form->createView()));
    }

    public function edit_commentAction($id_comment, $return_url, Request $request)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $message = '';
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('mycpBundle:comment')->find($id_comment);
        $comment->setComOwnershipCode($comment->getComOwnership()->getOwnMcpCode());
        $comment->setComUserEmail($comment->getComUser()->getUserEmail());
        $form = $this->createForm(new commentType, $comment);
        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_commenttype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $accommodation = $em->getRepository("mycpBundle:ownership")->findOneBy(array("own_mcp_code" => $comment->getComOwnershipCode()));
                $user = $em->getRepository("mycpBundle:user")->findOneBy(array("user_email" => $comment->getComUserEmail()));
                $userTourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array("user_tourist_user" => $user->getUserId()));

                if ($accommodation != null && $user != null && $userTourist != null) {
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
                } else {
                    $message = 'Error: No existe usuario con ese correo o no existe alojamiento con ese código';
                    $this->get('session')->getFlashBag()->add(self::MESSAGE_ERROR_TYPE, $message);
                    return $this->render('mycpBundle:comment:new.html.twig', array('form' => $form->createView(), 'edit_comment' => $comment->getComId()));
                }
            }
        }

        return $this->render('mycpBundle:comment:new.html.twig', array('form' => $form->createView(), 'edit_comment' => $comment->getComId()));
    }

    public function delete_commentAction($id_comment, $return_url)
    {
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

    public function get_comments_by_ownershipAction($id_own)
    {
        $em = $this->getDoctrine()->getManager();
        $comments = $em->getRepository('mycpBundle:comment')->findBy(array('com_ownership' => $id_own), array('com_public' => 'ASC', 'com_date' => "DESC"));
        return $this->render('mycpBundle:utils:comments.html.twig', array('comments' => $comments));
    }

    public function get_all_rateAction($post)
    {
        $selected = '';
        if (isset($post['selected']))
            $selected = $post['selected'];
        return $this->render('mycpBundle:utils:range_max_5_no_0.html.twig', array('selected' => $selected));
    }

    public function publicAction($id_comment, $return_url, Request $request)
    {
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
        $service_log->saveLog($comment->getLogDescription() . " (Publicado)", BackendModuleName::MODULE_COMMENT, log::OPERATION_UPDATE, DataBaseTables::COMMENT);

        if ($return_url == '' || $return_url == 'null' || $return_url == null)
            return $this->redirect($this->generateUrl('mycp_list_comments'));
        else
            return $this->redirect($this->generateUrl($return_url));
    }

    public function lastAction($items_per_page, Request $request)
    {
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

    public function publicSelectedCallbackAction()
    {
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
            $service_log->saveLog("Publicados " . count($ids) . ' comentarios', BackendModuleName::MODULE_COMMENT, log::OPERATION_UPDATE, DataBaseTables::COMMENT);

            $response = $this->generateUrl($returnUrl);
            //return $this->redirect($this->generateUrl($returnUrl));
        } catch (\Exception $e) {
            $message = 'Ha ocurrido un error durante la publicación de los comentarios.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            $response = "ERROR";
        }
        return new Response($response);
    }

    public function deleteSelectedCallbackAction()
    {
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
            $service_log->saveLog("Eliminados " . count($ids) . ' comentarios', BackendModuleName::MODULE_COMMENT, log::OPERATION_DELETE, DataBaseTables::COMMENT);

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

        $commentContent = $this->renderView('mycpBundle:comment:positiveComment.html.twig', array(
            'comment' => $comment,
            "isFromUserWithReservation" => $isFromUserWithReservation
        ));

        return new Response($commentContent, 200);
    }

    public function replicateCommentAction(Request $request)
    {
        $origin_code = $request->get('origin');
        $destination_code = $request->get('destination');
        $em = $this->getDoctrine()->getManager();
        $origin = $em->getRepository('mycpBundle:ownership')->createQueryBuilder('ownership')->where('ownership.own_mcp_code = :code')->setParameter('code', $origin_code)->getQuery()->getSingleResult();
        $destination = $em->getRepository('mycpBundle:ownership')->createQueryBuilder('ownership')->where('ownership.own_mcp_code = :code')->setParameter('code', $destination_code)->getQuery()->getSingleResult();
        if (!is_null($origin) && !is_null($destination)) {
            $originComments = $origin->getComments();
            if (!is_null($originComments) && count($originComments) > 0) {
                foreach ($originComments as $comment) {
                    $newComment = new comment();
                    $newComment->setComByClient($comment->getComByClient());
                    $newComment->setComComments($comment->getComComments());
                    $newComment->setComOwnership($destination);
                    $newComment->setComUser($comment->getComUser());
                    $newComment->setComDate($comment->getComDate());
                    $newComment->setComPublic($comment->getComPublic());
                    $newComment->setComRate($comment->getComRate());
                    $newComment->setPositive($comment->getPositive());
                    $em->persist($newComment);
                    $em->flush();
                }

            }

            $message = 'Comentarios replicados satisfactoriamente.';
            $message_type = 'message_ok';
            $success = true;
        } else {
            $message = 'Verifique los codigos de los alojamientos.';
            $message_type = 'message_error_main';
            $success = false;
        }
        $this->get('session')->getFlashBag()->add($message_type, $message);
        return new JsonResponse(array('success' => $success));

    }
}
