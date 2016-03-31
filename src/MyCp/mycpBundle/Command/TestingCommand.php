<?php

namespace MyCp\mycpBundle\Command;

use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Helpers\Images;

class TestingCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('mycp:testings')
                ->setDefinition(array())
                ->setDescription('Tests some functionalities')
                ->setHelp(<<<EOT
                Command <info>mycp_task:testings</info> do some tests.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $this->testingSMSNotifications($em, $output, $container);

        $output->writeln("End of testings");
    }

    private function testingSMSNotifications($em,OutputInterface $output, $container)
    {
        $notificationService = $container->get("mycp.notification.service");
        $reservation = $em->getRepository("mycpBundle:generalReservation")->find(69210);
        $notificationService->sendConfirmPaymentSMSNotification($reservation, true);
        $notificationService->sendCheckinSMSNotification($reservation, true);
    }

    private function testingPostPaymentProcess($em,OutputInterface $output)
    {
        $container = $this->getContainer();
        $bookingService = $container->get("front_end.services.booking");

        $booking = $em->getRepository("mycpBundle:booking")->find(1360);

        $bookingService->processPaymentEmails($booking, PaymentHelper::STATUS_PENDING);

    }

    private function testingActivationUrl($em,OutputInterface $output)
    {
        $user = $em->getRepository("mycpBundle:user")->find(2086);
        $activationUrl = $this->getActivationUrl($user, "es");
        $activationUrl = str_replace(".com//", ".com/", $activationUrl);
        $output->writeln($activationUrl);
    }

    private function getActivationUrl($user, $userLocale)
    {
        $encodedString = $this->getContainer()->get("Secure")->getEncodedUserString($user);
        $enableUrl = $this->getContainer()->get("router")->generate('frontend_enable_user', array(
            'string' => $encodedString,
            'locale' => $userLocale,
            '_locale' => $userLocale
        ), true);
        return $enableUrl;
    }

}