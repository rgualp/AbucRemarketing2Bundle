<?php
/**
 * Created by PhpStorm.
 * User: ernest
 * Date: 4/12/17
 * Time: 11:26 AM
 */

namespace MyCp\FrontEndBundle\Helpers;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class CacheListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
//        $response = $event->getResponse();
//
//        // marca la respuesta como pública o privada
//        $response->setPublic();
//        $response->setPrivate();
//
//        // fija la edad máxima de privado o compartido
//        $response->setMaxAge(600);
//        $response->setSharedMaxAge(600);
//
//        // fija una directiva Cache-Control personalizada
//        $response->headers->addCacheControlDirective('must-revalidate', true);
    }
}