<?php

namespace MyCp\MobileFrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FavoriteController extends Controller
{
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


        $response = $this->renderView('@MyCpMobileFrontend/favorite/itemLinkFavorite.html.twig', array(
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

        $response = $this->renderView('@MyCpMobileFrontend/favorite/itemLinkFavorite.html.twig', array(
            'is_in_favorite' => $em->getRepository('mycpBundle:favorite')->isInFavorite($element_id, ($favorite_type == "ownership"), $user_ids["user_id"], $user_ids["session_id"]),
            'favorite_type' => $favorite_type,
            'element_id' => $element_id,
            'list_preffix' => $list_preffix,
            'include_text' => $include_text
        ));

        return new Response($response, 200);
    }
}
