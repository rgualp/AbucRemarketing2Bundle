<?php

namespace MyCp\mycpBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LodgingControllerTest extends WebTestCase
{
    public function testLodging_front()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lodging_front');
    }

}
