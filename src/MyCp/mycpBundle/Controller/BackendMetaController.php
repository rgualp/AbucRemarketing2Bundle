<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\metaLang;
use MyCp\mycpBundle\Form\metaType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendMetaController extends Controller {

    function listAction(){
        $em=$this->getDoctrine()->getManager();

        $metas = $em->getRepository('mycpBundle:metaTag')->getAll();

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_METATAGS);

        return $this->render('mycpBundle:meta:list.html.twig', array(
            'metas' => $metas
                ));
    }

    function editAction($meta_id,Request $request) {
        $em=$this->getDoctrine()->getManager();
        $langs=$em->getRepository('mycpBundle:lang')->findAll();
        $metas=$em->getRepository('mycpBundle:metaTag')->getMetaLangs($meta_id);
        $form = $this->createForm(new metaType(),array('langs'=>$langs,'meta'=>$metas));
        $metaTag = $em->getRepository('mycpBundle:metaTag')->find($meta_id);

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
                    $meta_lang->setMetaTag($metaTag);
                    $em->persist($meta_lang);
                }
                $em->flush();
                $message = 'Meta Tags modificados satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Update metatag translations, ' . $meta_id, BackendModuleName::MODULE_METATAGS);

                return $this->redirect($this->generateUrl('mycp_list_metatags'));
            }
        }

        return $this->render(
            'mycpBundle:meta:edit.html.twig',
            array('form' => $form->createView(), 'meta_id' => $meta_id, 'metaTag' => $metaTag)
        );
    }


}
