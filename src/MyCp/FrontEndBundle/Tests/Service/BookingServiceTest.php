<?php
/**
 * Created by PhpStorm.
 * User: YANET
 * Date: 14/03/2016
 * Time: 3:36 PM
 */

namespace MyCp\FrontEndBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;

class BookingServiceTest extends WebTestCase
{
    private $service;
    private $container;

    public function init()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->container = $kernel->getContainer();
        $this->service = $this->container->get('front_end.services.booking');
    }

    public function testCalculateBookingDetails()
    {
        $this->init();

        // First, mock the object to be used in the test
        /*$booking = $this->getMock('\mycpBundle\Entity\Booking');
        $booking->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1000));

        // Now, mock the repository so it returns the mock of the employee
        $bookingRepository = $this
            ->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $bookingRepository->expects($this->once())
            ->method('find')
            ->will($this->returnValue($booking));

        // Last, mock the EntityManager to return the mock of the repository
        $entityManager = $this
            ->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($bookingRepository));*/


        $details = $this->service->calculateBookingDetails(1000);

        var_dump($details);
    }
}