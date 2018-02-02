<?php

namespace MyCp\FrontEndBundle\Controller;

use BeSimple\I18nRoutingBundle\Tests\Routing\RouterTest;
use MyCp\FrontEndBundle\Helpers\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LanguageController extends Controller
{
    public function getLanguagesAction($route, $routeParams = null)
    {
        $routeParams = empty($routeParams) ? array() : $routeParams;
        $arrTransDest = array();

        $em = $this->getDoctrine()->getManager();
        $languages = $em
            ->getRepository('mycpBundle:lang')
            ->findBy(array('lang_active' => 1), array('lang_name' => 'ASC'));





        if ($route == 'frontend_details_destination'){
            $routeParamsDes = json_decode(urldecode($routeParams));
            $destination_name = str_replace('-', ' ', $routeParamsDes->destination_name);
            $lang = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $routeParamsDes->_locale));
            $destinationsLang = $em->getRepository('mycpBundle:destinationLang')->findOneBy(array('des_lang_name'=>$destination_name,'des_lang_lang'=>$lang->getLangId()));

            if ($destinationsLang){
                $destination = $destinationsLang->getDesLangDestination();

                foreach ($languages as $language){
                    $code = strtolower($language->getLangCode());
                    $destinationsLang = $em->getRepository('mycpBundle:destinationLang')->findOneBy(array('des_lang_destination'=>$destination->getDesId(),'des_lang_lang'=>$language->getLangId()));
                    if ($destinationsLang && $destinationsLang->getDesLangName() != ''){
                        $url_destination_name = Utils::convert_text($destinationsLang->getDesLangName());
                        $url_destination_name = strtolower($url_destination_name);
                        $url_destination_name = str_replace(' ', '-', $url_destination_name);
                        $routeParamsDes->destination_name = $url_destination_name;
                        $arrTransDest[$code] = urlencode(json_encode($routeParamsDes));
                    }else{
                        $arrTransDest[$code] = $routeParams;
                    }
                }

                $response = $this->render('FrontEndBundle:language:languages.html.twig', array(
                    'languages' => $languages,
                    'route' => $route,
                    'routeParams' => $routeParams,
                    'arrTransDest' => $arrTransDest
                ));

                // cache controol -> languages rarely change
                $response->setSharedMaxAge(3600);

                return $response;
            }

        }

        $languages = $em
            ->getRepository('mycpBundle:lang')
            ->findBy(array('lang_active' => 1), array('lang_name' => 'ASC'));

        $response = $this->render('FrontEndBundle:language:languages.html.twig', array(
                'languages' => $languages,
                'route' => $route,
                'routeParams' => $routeParams
            ));

        // cache controol -> languages rarely change
        $response->setSharedMaxAge(3600);

        return $response;
    }

    public function changeAction($lang, $route, $routeParams = null)
    {
        $em = $this->getDoctrine()->getManager();


//        $routeParams = $this->getRequest()->get('_route_params');
        $routeParams = empty($routeParams) ? array() : json_decode(urldecode($routeParams), true);
        $routeParams['_locale'] = $lang;
        $routeParams['locale'] = $lang;
        $newRoute = $this->get('router')->generate($route, $routeParams);

        //Guardar en userTourist el lenguaje q cambio
        $lang = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $lang));
        $user = $this->getUser();

        if (!empty($user) && $user != 'anon.') {
            $userTourist = $em
                ->getRepository('mycpBundle:userTourist')
                ->findOneBy(array('user_tourist_user' => $user->getUserId()));

            // if the user is not a tourist (e.g. a staff member), the
            // userTourist does not exist
            if(isset($userTourist)) {
                $userTourist->setUserTouristLanguage($lang);
                $em->persist($userTourist);
                $em->flush();
            }
        }

        return $this->redirect($newRoute);
    }
}
