<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FavoriteController extends Controller {

    public function insertAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $favorite_type = $request->request->get("favorite_type");
        $element_id = $request->request->get("element_id");
        $list_preffix = $request->request->get("list_preffix");
        $include_text = $request->request->get("include_text");

        $data = array();
        $data['favorite_user_id'] = $user_ids["user_id"];
        $data['favorite_session_id'] = $user_ids["session_id"];
        $data['favorite_ownership_id'] = ($favorite_type == "ownership") ? $element_id : null;
        $data['favorite_destination_id'] = ($favorite_type == "destination") ? $element_id : null;

        $em->getRepository('mycpBundle:favorite')->insert($data);

        $response = $this->renderView('FrontEndBundle:favorite:itemLinkFavorite.html.twig', array(
            'is_in_favorite' => $em->getRepository('mycpBundle:favorite')->isInFavorite($element_id, ($favorite_type == "ownership"), $user_ids["user_id"], $user_ids["session_id"]),
            'favorite_type' => $favorite_type,
            'element_id' => $element_id,
            'list_preffix' => $list_preffix,
            'include_text' => $include_text
        ));

        return new Response($response, 200);
    }

    public function deleteAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $favorite_type = $request->request->get("favorite_type");
        $element_id = $request->request->get("element_id");
        $list_preffix = $request->request->get("list_preffix");
        $include_text = $request->request->get("include_text");

        $data = array();
        $data['favorite_user_id'] = $user_ids["user_id"];
        $data['favorite_session_id'] = $user_ids["session_id"];
        $data['favorite_ownership_id'] = ($favorite_type == "ownership") ? $element_id : null;
        $data['favorite_destination_id'] = ($favorite_type == "destination") ? $element_id : null;

        $em->getRepository('mycpBundle:favorite')->delete($data);

        $response = $this->renderView('FrontEndBundle:favorite:itemLinkFavorite.html.twig', array(
            'is_in_favorite' => $em->getRepository('mycpBundle:favorite')->isInFavorite($element_id, ($favorite_type == "ownership"), $user_ids["user_id"], $user_ids["session_id"]),
            'favorite_type' => $favorite_type,
            'element_id' => $element_id,
            'list_preffix' => $list_preffix,
            'include_text' => $include_text
        ));

        return new Response($response, 200);
    }

    public function details_insertAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $favorite_type = $request->request->get("favorite_type");
        $element_id = $request->request->get("element_id");

        $data = array();
        $data['favorite_user_id'] = $user_ids["user_id"];
        $data['favorite_session_id'] = $user_ids["session_id"];
        $data['favorite_ownership_id'] = ($favorite_type == "ownership") ? $element_id : null;
        $data['favorite_destination_id'] = ($favorite_type == "destination") ? $element_id : null;

        $em->getRepository('mycpBundle:favorite')->insert($data);

        $response = $this->renderView('FrontEndBundle:favorite:detailsFavorite.html.twig', array(
            'is_in_favorite' => $em->getRepository('mycpBundle:favorite')->isInFavorite($element_id, ($favorite_type == "ownership"), $user_ids["user_id"], $user_ids["session_id"]),
            'favorite_type' => $favorite_type,
            'element_id' => $element_id
        ));

        return new Response($response, 200);
    }

    public function details_deleteAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $favorite_type = $request->request->get("favorite_type");
        $element_id = $request->request->get("element_id");

        $data = array();
        $data['favorite_user_id'] = $user_ids["user_id"];
        $data['favorite_session_id'] = $user_ids["session_id"];
        $data['favorite_ownership_id'] = ($favorite_type == "ownership") ? $element_id : null;
        $data['favorite_destination_id'] = ($favorite_type == "destination") ? $element_id : null;

        $em->getRepository('mycpBundle:favorite')->delete($data);

        $response = $this->renderView('FrontEndBundle:favorite:detailsFavorite.html.twig', array(
            'is_in_favorite' => $em->getRepository('mycpBundle:favorite')->isInFavorite($element_id, ($favorite_type == "ownership"), $user_ids["user_id"], $user_ids["session_id"]),
            'favorite_type' => $favorite_type,
            'element_id' => $element_id
        ));

        return new Response($response, 200);
    }

    public function delete_from_listAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $session = $request->getSession();

        $favorite_type = $request->request->get("favorite_type");
        $element_id = $request->request->get("element_id");

        $data = array();
        $data['favorite_user_id'] = $user_ids["user_id"];
        $data['favorite_session_id'] = $user_ids["session_id"];
        $data['favorite_ownership_id'] = ($favorite_type == "ownership" || $favorite_type == "ownershipfav") ? $element_id : null;
        $data['favorite_destination_id'] = ($favorite_type == "destination") ? $element_id : null;

        $em->getRepository('mycpBundle:favorite')->delete($data);

        if ($favorite_type == "ownership" or $favorite_type == "ownershipfav") {
            $ownership_favorities = $em->getRepository('mycpBundle:favorite')->getFavoriteAccommodations($user_ids["user_id"], $user_ids["session_id"]);
            $session->set('user_fav_own_count', count($ownership_favorities));

            if($favorite_type == "ownership")
            $response = $this->renderView('FrontEndBundle:ownership:ownershipArrayItemList.html.twig', array(
                'list' => $ownership_favorities,
                'list_preffix' => 'own_favorities',
                'is_in_favorites_list' => true
            ));
            else if($favorite_type == "ownershipfav")
                $response = $this->renderView('FrontEndBundle:favorite:ownershipArrayItemFavorite.html.twig', array(
                'list' => $ownership_favorities,
                'list_preffix' => 'own_favorities',
                'is_in_favorites_list' => true
            ));
        } else if ($favorite_type == "destination") {
            $destination_favorities = $em->getRepository('mycpBundle:favorite')->getFavoriteDestinations($user_ids["user_id"], $user_ids["session_id"]);
            $session->set('user_fav_dest_count', count($destination_favorities));
            $response = $this->renderView('FrontEndBundle:destination:arrayItemListDestination.html.twig', array(
                'list' => $destination_favorities,
                'list_preffix' => 'own_favorities',
                'is_in_favorites_list' => true
            ));
        }

        return new Response($response, 200);
    }

    public function listAction() {
        $em = $this->getDoctrine()->getManager();
        $locale = $this->get('translator')->getLocale();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);

        if ($user_ids["user_id"] == null || $user_ids["user_id"] == "anon.") {
            $ownership_favorities = $em->getRepository('mycpBundle:favorite')->getFavoriteAccommodations($user_ids["user_id"], $user_ids["session_id"]);
            $destination_favorities = $em->getRepository('mycpBundle:favorite')->getFavoriteDestinations($user_ids["user_id"], $user_ids["session_id"], null, null, strtoupper($locale));

            return $this->render('FrontEndBundle:favorite:listFavorities.html.twig', array(
                        'ownership_favorities' => $ownership_favorities,
                        'destination_favorities' => $destination_favorities,
            ));
        } else {
            $request = $this->getRequest();
            $session = $request->getSession();

            if ($session->get('mycasatrip_favorite_type') == null)
                $session->set('mycasatrip_favorite_type', 'ownerships');

            return $this->redirect($this->generateUrl('frontend_mycasatrip_favorites_accomodations'));
        }
    }

}
