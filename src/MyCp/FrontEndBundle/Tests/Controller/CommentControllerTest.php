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

        /*$request = array();
        $request["com_ownership_id"] = 3;
        $request["com_rating"] = 5;
        $request["com_comments"] = "This comment is inserted by a functional test";

        $crawler = $client->request('GET', '/en/insert-comment/3', $request);*/
        $crawler = $client->request('GET', '/en/lodging-where-cuba-cuba-is/');

        $this->assertTrue($client->getResponse()->isSuccessful());


        /*$this->client->enableProfiler();
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
        // Check that an email was sent
        $this->assertEquals(1, $mailCollector->getMessageCount(), sprintf(
            'Se han enviado %s correos',
            $mailCollector->getMessageCount()
        ));*/

        /*$collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        var_dump($message->getBody());*/

        // Asserting email data
        /*$this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('Hello Email', $message->getSubject());
        $this->assertEquals('send@example.com', key($message->getFrom()));
        $this->assertEquals('recipient@example.com', key($message->getTo()));
        $this->assertEquals(
            'You should see me from the profiler!',
            $message->getBody()
        );*/

        /*if ($profile = $client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(
                3,
                $profile->getCollector('db')->getQueryCount(),
                sprintf(
                    'La cantidad de consultas a base de datos es menor que 3 (token %s)',
                    $profile->getToken()
                )
            );

        }*/
    }
}
