<?php

namespace MyCp\FrontEndBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;

class CommentControllerTest extends WebTestCase
{
    private $client = null;
    private $container = null;
    private $em = null;

    public function login($provider)
    {
        $client = static::createClient();
        $container = static::$kernel->getContainer();
        $session = $container->get('session');
        $user = self::$kernel->getContainer()->get('doctrine')->getRepository('mycpBundle:user')->findOneBy(array("user_name" => "Yanet"));

        $token = new UsernamePasswordToken($user, null, $provider, $user->getRoles());
        $session->set('_security_'.$provider, serialize($token));
        $session->save();

        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        /*
         * array('ROLE_CLIENT_TOURIST')
         * */

        return $client;
    }

    public function testInsert()
    {
        $client = $this->login("frontend");

        $request = array();
        $request["com_ownership_id"] = 3;
        $request["com_rating"] = 5;
        $request["com_comments"] = "This comment is inserted by a functional test";

        $crawler = $client->request('GET', '/en/insert-comment/3', $request);
        //$crawler = $client->request('GET', '/en/lodging-where-cuba-cuba-is/');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
