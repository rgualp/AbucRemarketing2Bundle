<?php

namespace MyCp\MobileFrontendBundle\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class LanguageMobileController extends Controller
{

    public function setLocale($newLocale ) {
        $request = $this->getRequest();

        // get last requested path
        $referer = $request->headers->get('referer');
        $logger = $this->container->get('logger');
        $logger->warning($this->getRequest()->getHost());
        //get Host
        $lastPath = substr($referer, strpos($referer, $request->getHost()));
        $lastPath = str_replace($request->getHost(),'', $lastPath);

        // get last route
        $matcher = $this->get('router');
        $parameters = $matcher->match($lastPath);

        // set new locale (to session and to the route parameters)
        $parameters['_locale'] = $newLocale;
        $parameters['locale']=$newLocale;

        // default parameters has to be unsetted!
        $route = $parameters['_route'];
        unset($parameters['_route']);
        unset($parameters['_controller']);


        return $this->generateUrl($route, $parameters);
    }
    public function getLanguagesAction($route, $routeParams = null)
    {
        $routeParams = empty($routeParams) ? array() : $routeParams;
        $arrTransDest = array();

        $em = $this->getDoctrine()->getManager();
        $languages = $em
            ->getRepository('mycpBundle:lang')
            ->findBy(array('lang_active' => 1), array('lang_name' => 'ASC'));



        $languages = $em
            ->getRepository('mycpBundle:lang')
            ->findBy(array('lang_active' => 1), array('lang_name' => 'ASC'));

        $response = $this->render('MyCpMobileFrontendBundle:utils:languages.html.twig', array(
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



        $newlang=$lang;

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


        return $this->redirect($this->setLocale($newlang));
    }
}
