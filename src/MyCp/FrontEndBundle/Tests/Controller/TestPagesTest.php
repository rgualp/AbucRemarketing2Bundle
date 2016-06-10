<?php

namespace MyCp\FrontEndBundle\Tests\Controller;

use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class TestPagesTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @dataProvider provideUrls
     *
     */
    public function testPageIsSuccessful($route, $url)
    {
        $cookie = new Cookie('mycp_user_session', 'test-value');
        $this->client->getCookieJar()->set($cookie);

        $this->client->followRedirects();
        $this->client->request('GET', $url);
        $response= $this->client->getResponse();
        $isSuccessful= $response->isSuccessful();
        $statusCode= $response->getStatusCode();
        while($statusCode==302){
            $crawler= $this->client->followRedirect();
            $response= $this->client->getResponse();
            $statusCode= $response->getStatusCode();
        }

        //405:Method Not Allowed, implementar una recursividad con POST
        if($statusCode!=405 && !$isSuccessful){
//            print_r('Failed:'.$statusCode.'->'.$route.'->'.$url."<br>");
            $this->assertTrue($isSuccessful);
        }
        else{
            $this->assertEquals(1, 1, "Ever TRUE");
        }

    }

    public function provideUrls()
    {
        $client = self::createClient();
        $router= $client->getContainer()->get('router');
        $routes = $router->getRouteCollection()->all();

        $routesArr=array();
        foreach($routes as $key=>$value){
            if ($key[0]!='_'){
                try{
                    $url = $router->generate(''.$key);
                    $routesArr[]=array($key, $url);
                    //print $key.'->'.$url."\n";
                }
                catch(\Exception $error){
                }
            }
        }
        return $routesArr;
    }
}
