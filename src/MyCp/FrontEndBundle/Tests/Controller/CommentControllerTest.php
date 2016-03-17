<?php

namespace MyCp\FrontEndBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;

class CommentControllerTest extends TestBaseMyCp
{
	public function testInsert()
	{
		$user= $this->em->getRepository('mycpBundle:user')->findOneBy(array("user_email" => "yanet.moralesr@gmail.com"));
		$this->createAuthorizedClient($user);

		$request = array();
		$request["com_ownership_id"] = 3;
		$request["com_rating"] = 5;
		$request["com_comments"] = "This comment is inserted by a functional test".rand();

		$this->client->enableProfiler();
		$this->client->request('GET', '/en/insert-comment/3', $request);

		$mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
		$collectedMessages = $mailCollector->getMessages();
		$message = $collectedMessages[0];

//		foreach($collectedMessages as $message){
//			echo '--';
//			echo $message->getBody();
//			echo $message->getSubject();
//			echo key($message->getFrom());
//			echo key($message->getTo());
//			echo '--';
//		}
//		exit;

		$this->assertEquals(2, $mailCollector->getMessageCount());
		$this->assertInstanceOf('Swift_Message', $message);
		$this->assertEquals('Nuevos comentarios recibidos', $message->getSubject());
		$this->assertEquals('noreply@mycasaparticular.com', key($message->getFrom()));
		$this->assertTrue($this->client->getResponse()->isSuccessful());
	}
}
