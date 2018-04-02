<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 2/04/18
 * Time: 14:05
 */

namespace MyCp\mycpBundle\Events;


use Doctrine\Common\Persistence\ObjectManager;
use MyCp\FrontEndBundle\Helpers\Utils;
use MyCp\mycpBundle\Helpers\FileIO;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Validator\Constraints\File;

class SiteMapListener
{
    const CONFIGURATION_DIR_ADDITIONALS_FILES = "configuration.dir.additionalsFiles";
    const SITE_MAP = "sitemap.xml";
    private $em;
    private $container;

    /**
     * SiteMapEvent constructor.
     * @param ObjectManager $entityManager
     * @param Container $container
     */
    public function __construct(ObjectManager $entityManager, $container)
    {
        $this->em = $entityManager;
        $this->container = $container;
    }

    public function onEvent()
    {
        $hostname = $this->container->get('request')->getHost();
        $languages = $this->em->getRepository('mycpBundle:lang')->findBy(array('lang_active' => 1));
        //houses
//        $url_houses = array();
//        $houses = $this->em->getRepository('mycpBundle:ownership')->findBy(array('own_status' => \MyCp\mycpBundle\Entity\ownershipStatus::STATUS_ACTIVE));
//
//        foreach ($languages as $lang) {
//            $routingParams = array('locale' => strtolower($lang->getLangCode()), '_locale' => strtolower($lang->getLangCode()));
//            $url = array(
//                'loc' => $this->container->get('router')->generate('frontend_search_ownership', $routingParams),
//                'priority' => '0.8',
//                'changefreq' => 'monthly'
//            );
//            array_push($url_houses, $url);
//            foreach ($houses as $house) {
//                $house_name = Utils::urlNormalize($house->getOwnName());
//                $loc = $this->container->get('router')->generate('frontend_details_ownership', array_merge($routingParams, array('own_name' => $house_name)));
//                $url = array(
//                    'loc' => $loc,
//                    'priority' => '1.0',
//                    'changefreq' => 'monthly'
//                );
//                array_push($url_houses, $url);
//            }
//        }

        //destinations
        $url_destinations = array();
//        array_push($url_destinations, $url);
        foreach ($languages as $lang) {
            $routingParams = array('locale' => strtolower($lang->getLangCode()), '_locale' => strtolower($lang->getLangCode()));
            $loc = $this->container->get('router')->generate('frontend_list_destinations', $routingParams);
            $url = array(
                'loc' => $loc,
                'priority' => '0.8',
                'changefreq' => 'monthly'
            );
            array_push($url_destinations, $url);
            $destinations = $this->em->getRepository('mycpBundle:destination')->getLangMainMenu($lang->getLangCode());
            foreach ($destinations as $destination) {
                if ($destination['d_lang_name'] == '') {
                    $ulr_name = $destination['dest']->getDesName();
                } else {
                    $ulr_name = $destination['d_lang_name'];
                }

                $destination_name = Utils::urlNormalize($ulr_name);
                $url = array(
                    'loc' => $this->container->get('router')->generate('frontend_details_destination',
                        array_merge($routingParams, array('destination_name' => $destination_name))),
                    'priority' => '0.8',
                    'changefreq' => 'monthly'
                );
                array_push($url_destinations, $url);
            }
        }

        // site pages
        $url_sites = array();
        foreach ($languages as $lang) {
            $routingParams = array('locale' => strtolower($lang->getLangCode()), '_locale' => strtolower($lang->getLangCode()));

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend-welcome', $routingParams),
                'priority' => '1.0',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_how_it_works_information', $routingParams),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_list_favorite', $routingParams),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_register_user', $routingParams),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_get_with_reservations_municipality', $routingParams),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_voted_best_list_ownership', $routingParams),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_about_us', $routingParams),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_contact_user', $routingParams),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_list_faq', $routingParams),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_type_list_ownership',
                    array_merge($routingParams, array('type' => 'penthouse'))),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_type_list_ownership',
                    array_merge($routingParams, array('type' => 'villa-con-piscina'))),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_type_list_ownership',
                    array_merge($routingParams, array('type' => 'casa-particular'))),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_type_list_ownership',
                    array_merge($routingParams, array('type' => 'apartamento'))),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_type_list_ownership',
                    array_merge($routingParams, array('type' => 'propiedad-completa'))),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_legal_terms', $routingParams),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_security_privacity', $routingParams),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_sitemap_information', $routingParams),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );
            array_push($url_sites, $url);

            //login
            $url = array(
                'loc' => $this->container->get('router')->generate('frontend_public_login', $routingParams),
                'priority' => '0.5',
                'changefreq' => 'daily'
            );

            array_push($url_sites, $url);
        }

        $urls = array_merge($url_sites, $url_destinations);
        $this->generateSiteMapXML($urls, $hostname);
    }

    private function generateSiteMapXML($url_sites, $hostname)
    {

        $rootNode = new \SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\"></urlset>");
        foreach ($url_sites as $url) {
            $itemNode = $rootNode->addChild('url');
            $itemNode->addChild('loc', 'https://' . $hostname . $url['loc']);
            $itemNode->addChild('changefreq', $url['changefreq']);
            $itemNode->addChild('priority', $url['priority']);
        }
        $pathToFile = $this->container->getParameter(self::CONFIGURATION_DIR_ADDITIONALS_FILES);
        FileIO::deleteFile($pathToFile . "/" . self::SITE_MAP);
        FileIO::createDirectoryIfNotExist($pathToFile);
        $fp = fopen($pathToFile . "/" . self::SITE_MAP, "wb");
        fwrite($fp, $rootNode->asXML());
        fclose($fp);
    }

    private function getUrlsDestinations()
    {

    }

    private function getUrlsAccomodations()
    {

    }

    private function getUrlsSite()
    {

    }


}