<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\metaLang;
use MyCp\mycpBundle\Form\metaType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\log;

class BackendMetaController extends Controller {

    function edit_meta_tagsAction(Request $request) {
        $em=$this->getDoctrine()->getManager();
        $langs=$em->getRepository('mycpBundle:lang')->findAll();
        $metas=$em->getRepository('mycpBundle:metaLang')->findAll();
        $form = $this->createForm(new metaType(),array('langs'=>$langs,'meta'=>$metas));

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                foreach($metas as $meta)
                {
                    $em->remove($meta);
                }
                $form_submit=$request->get('mycp_mycpbundle_metatype');
                foreach ($langs as $lang) {
                    $meta_lang = new metaLang();
                    $meta_lang->setMetaLangLang($lang);
                    $meta_lang->setMetaLangDescription($form_submit['description_' . $lang->getLangId()]);
                    $meta_lang->setMetaLangKeywords($form_submit['keywords_' . $lang->getLangId()]);
                    $em->persist($meta_lang);
                }
                $em->flush();
                $message = 'Meta Tags modificados satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                return $this->redirect($this->generateUrl('mycp_edit_metatags'));
            }
        }

        return $this->render(
            'mycpBundle:meta:edit.html.twig',
            array('form' => $form->createView())
        );
    }


}
