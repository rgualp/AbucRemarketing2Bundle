<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 2/04/18
 * Time: 14:05
 */

namespace MyCp\mycpBundle\Listener;


use MyCp\mycpBundle\Service\SiteMapService;

class SiteMapListener
{
    private $service;

    /**
     * SiteMapEvent constructor.
     * @param SiteMapService $service
     */
    public function __construct(SiteMapService $service)
    {
        $this->service = $service;
    }

    public function onEvent()
    {
        $this->service->generateSiteMap();
    }


}