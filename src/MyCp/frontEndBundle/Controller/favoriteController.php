<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class favoriteController extends Controller {

     public function insertAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $user_ids = $em->getRepository('mycpBundle:favorite')->user_ids($this);
        $favorite_type = $request->request->get("favorite_type");
        $element_id = $request->request->get("element_id");
        $list_preffix = $request->request->get("list_preffix");
        
        $data = array();
        $data['favorite_user_id'] = $user_ids["user_id"];
        $data['favorite_session_id'] = $user_ids["session_id"];
        $data['favorite_ownership_id'] = ($favorite_type == "ownership") ? $element_id : null;
        $data['favorite_destination_id'] = ($favorite_type == "destination") ? $element_id : null;

        $em->getRepository('mycpBundle:favorite')->insert($data);

        $response = $this->renderView('frontEndBundle:favorite:itemLinkFavorite.html.twig', array(
            'is_in_favorite' => $em->getRepository('mycpBundle:favorite')->is_in_favorite($element_id,($favorite_type == "ownership"),$user_ids["user_id"], $user_ids["session_id"]),
            'favorite_type' => $favorite_type,
            'element_id' => $element_id,
            'list_preffix' => $list_preffix
                ));

        return new Response($response, 200);
    }
    
    public function deleteAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $user_ids = $em->getRepository('mycpBundle:favorite')->user_ids($this);
        $favorite_type = $request->request->get("favorite_type");
        $element_id = $request->request->get("element_id");
        $list_preffix = $request->request->get("list_preffix");

        $data = array();
        $data['favorite_user_id'] = $user_ids["user_id"];
        $data['favorite_session_id'] = $user_ids["session_id"];
        $data['favorite_ownership_id'] = ($favorite_type == "ownership") ? $element_id : null;
        $data['favorite_destination_id'] = ($favorite_type == "destination") ? $element_id : null;

        $em->getRepository('mycpBundle:favorite')->delete($data);

        $response = $this->renderView('frontEndBundle:favorite:itemLinkFavorite.html.twig', array(
            'is_in_favorite' => $em->getRepository('mycpBundle:favorite')->is_in_favorite($element_id,($favorite_type == "ownership"),$user_ids["user_id"], $user_ids["session_id"]),
            'favorite_type' => $favorite_type,
            'element_id' => $element_id,
            'list_preffix' => $list_preffix
                ));

        return new Response($response, 200);
    }

    public function listAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $locale = $this->get('translator')->getLocale();

        $user_ids = $em->getRepository('mycpBundle:favorite')->user_ids($this);

        $favorite_own_ids = $em->getRepository('mycpBundle:favorite')->get_element_id_list(true, $user_ids["user_id"], $user_ids["session_id"]);
        $favorite_destination_ids = $em->getRepository('mycpBundle:favorite')->get_element_id_list(false, $user_ids["user_id"], $user_ids["session_id"]); 
        
        $ownership_favorities = $em->getRepository('mycpBundle:ownership')->getListByIds($favorite_own_ids);
        $ownership_favorities_photos = $em->getRepository('mycpBundle:ownership')->get_photos_array($ownership_favorities);
        $ownership_favorities_rooms = $em->getRepository('mycpBundle:ownership')->get_rooms_array($ownership_favorities);
        
        $ownership_favorities_is_in = array();
        
        foreach ($ownership_favorities as $favorite) {
            $ownership_favorities_is_in[$favorite->getOwnId()] = true;
        }
        
        $destination_favorities = $em->getRepository('mycpBundle:destination')->get_list_by_ids($favorite_destination_ids);
        $destination_favorities_photos = $em->getRepository('mycpBundle:destination')->get_destination_photos($destination_favorities);
        $destination_favorities_localization = $em->getRepository('mycpBundle:destination')->get_destination_location($destination_favorities);
        $destination_favorities_statistics = $em->getRepository('mycpBundle:destination')->get_destination_owns_statistics($destination_favorities);
        $destination_favorities_description = $em->getRepository('mycpBundle:destination')->get_destination_description($destination_favorities,$locale);
        
        return $this->render('frontEndBundle:favorite:listFavorities.html.twig', array(
            'ownership_favorities' => $ownership_favorities,
            'ownership_favorities_photos' => $ownership_favorities_photos,
            'ownership_favorities_rooms' => $ownership_favorities_rooms,
            'ownership_favorities_is_in' => $ownership_favorities_is_in,
            'destination_favorities'=>$destination_favorities,
            'destination_favorities_photos'=>$destination_favorities_photos,
            'destination_favorities_localization'=>$destination_favorities_localization,
            'destination_favorities_statistics' => $destination_favorities_statistics,
            'destination_favorities_description' => $destination_favorities_description
        ));
    }

}
