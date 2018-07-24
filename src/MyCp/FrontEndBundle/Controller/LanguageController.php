<?php

namespace MyCp\FrontEndBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class LanguageController extends Controller
{

    const DEV_ENVIRONMENT = "dev";

    public function getLanguagesAction($route, $routeParams = null)
    {
        $routeParams = empty($routeParams) ? array() : $routeParams;
        $arrTransDest = array();

        $em = $this->getDoctrine()->getManager();
        $languages = $em
            ->getRepository('mycpBundle:lang')
            ->findBy(array('lang_active' => 1), array('lang_name' => 'ASC'));


//        if ($route == 'frontend_details_destination'){
//            $routeParamsDes = json_decode(urldecode($routeParams));
//            $destination_name = str_replace('-', ' ', $routeParamsDes->destination_name);
//            $lang = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $routeParamsDes->_locale));
//            $destinationsLang = $em->getRepository('mycpBundle:destinationLang')->findOneBy(array('des_lang_name'=>$destination_name,'des_lang_lang'=>$lang->getLangId()));
//
//            if ($destinationsLang){
//                $destination = $destinationsLang->getDesLangDestination();
//
//                foreach ($languages as $language){
//                    $code = strtolower($language->getLangCode());
//                    $destinationsLang = $em->getRepository('mycpBundle:destinationLang')->findOneBy(array('des_lang_destination'=>$destination->getDesId(),'des_lang_lang'=>$language->getLangId()));
//                    if ($destinationsLang && $destinationsLang->getDesLangName() != ''){
//                        $url_destination_name = Utils::convert_text($destinationsLang->getDesLangName());
//                        $url_destination_name = strtolower($url_destination_name);
//                        $url_destination_name = str_replace(' ', '-', $url_destination_name);
//                        $routeParamsDes->destination_name = $url_destination_name;
//                        $arrTransDest[$code] = urlencode(json_encode($routeParamsDes));
//                    }else{
//                        $arrTransDest[$code] = $routeParams;
//                    }
//                }
//
//                $response = $this->render('FrontEndBundle:language:languages.html.twig', array(
//                    'languages' => $languages,
//                    'route' => $route,
//                    'routeParams' => $routeParams,
//                    'arrTransDest' => $arrTransDest
//                ));
//
//                // cache controol -> languages rarely change
//                $response->setSharedMaxAge(3600);
//
//                return $response;
//            }
//
//        }

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
        $newlang = $lang;
        //Guardar en userTourist el lenguaje q cambio
        $lang = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $lang));
        $user = $this->getUser();
        if (!empty($user) && $user != 'anon.') {
            $userTourist = $em
                ->getRepository('mycpBundle:userTourist')
                ->findOneBy(array('user_tourist_user' => $user->getUserId()));

            // if the user is not a tourist (e.g. a staff member), the
            // userTourist does not exist
            if (isset($userTourist)) {
                $userTourist->setUserTouristLanguage($lang);
                $em->persist($userTourist);
                $em->flush();
            }
        }
        return $this->setLocale($newlang);
    }

    public function setLocale($newLocale)
    {
        $request = $this->get('request');
        // get last requested path
        $referer = $request->headers->get('referer');
        $logger = $this->container->get('logger');
        $logger->warning($this->get('request')->getHost());
        $parameters = array();

        if (!is_null($referer) && $referer != "") {
            //get Host
            $lastPath = substr($referer, strpos($referer, $request->getHost()));
            $lastPath = str_replace($request->getHost(), '', $lastPath);
            $environment = $this->get('service_container')->getParameter("kernel.environment");
            if ($environment == self::DEV_ENVIRONMENT) {
                $lastPath = str_replace('/app_dev.php/', '/', $lastPath);
            }
            $matcher = $this->get('router');
            $parameters = $matcher->match($lastPath);
        }

        // set new locale (to session and to the route parameters)
        $parameters['_locale'] = $newLocale;
        $parameters['locale'] = $newLocale;
        $route = $parameters['_route'];

        unset($parameters['_route']);
        unset($parameters['_controller']);
        return new Response($this->generateUrl($route, $parameters), Response::HTTP_OK);
    }
}
