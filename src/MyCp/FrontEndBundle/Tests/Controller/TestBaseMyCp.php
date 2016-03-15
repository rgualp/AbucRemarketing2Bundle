<?php
/**
 * Created by PhpStorm.
 * User: arieskien
 * Date: 15/03/16
 * Time: 12:40
 */

namespace MyCp\FrontEndBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TestBaseMyCp extends WebTestCase
{
	/**
	 * @var \Symfony\Bundle\FrameworkBundle\Client
	 */
	protected $client = null;

	/**
	 *  @var \Doctrine\Common\Persistence\ObjectManager
	 */
	protected $em = null;

	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;

	public function setUp()
	{
		$this->client = static::createClient();
		$this->em = $this->client->getContainer()->get('doctrine')->getManager();
		$this->container = static::$kernel->getContainer();
	}

	protected function createAuthorizedClient($user, $firewall='user')
	{
		$session = $this->container->get('session');

		$token = new UsernamePasswordToken($user, null, 'provider_name', $user->getRoles());
		$session->set('_security_'.$firewall, serialize($token));
		$session->save();

		$this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

		return $this->client;
	}

}