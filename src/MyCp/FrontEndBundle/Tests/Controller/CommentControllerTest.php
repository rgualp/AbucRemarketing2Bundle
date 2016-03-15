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

    public function setUp()
    {
        $this->client = static::createClient();
    }

    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'frontend';

        $user = $this->client->getContainer()->get('doctrine')->getManager()->getRepository('mycpBundle:user')
            ->find(2696);

        $token = new UsernamePasswordToken($user, null, $firewall, array('ROLE_CLIENT_TOURIST'));

        $session->set('_security_'.$firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());

        $this->client->getCookieJar()->set($cookie);
    }

    /*public function login($provider)
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

        /*return $client;
    }*/

    public function testInsert()
    {
        $this->logIn();

        $request = array();
        $request["com_ownership_id"] = 3;
        $request["com_rating"] = 5;
        $request["com_comments"] = "This comment is inserted by a functional test";

        $crawler = $this->client->request('GET', '/en/insert-comment/3/', $request);
        //$crawler = $client->request('GET', '/en/lodging-where-cuba-cuba-is/');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }
}
