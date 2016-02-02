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
                ->addArgument("bookingID", InputArgument::REQUIRED)
                ->setDescription('Tests some functionalities')
                ->setHelp(<<<EOT
                Command <info>mycp_task:testings</info> do some tests.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $service = $container->get('front_end.services.booking');

        $bookingId = $input->getArgument("bookingID");

        $booking = $em->getRepository("mycpBundle:booking")->find($bookingId);

        $service->postProcessBookingPayment($booking, PaymentHelper::STATUS_PENDING);

       /* $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $reservations = $em->getRepository("mycpBundle:generalReservation")->findAll();
        $output->writeln("1. Testing shallSendOutFeedbackReminderEmail...");

        foreach ($reservations as $res) {
            $result = $em->getRepository("mycpBundle:generalReservation")->shallSendOutFeedbackReminderEmail($res);
            $userId = $res->getGenResUserId()->getUserId();
            $ownershipId = $res->getGenResOwnId()->getOwnId();
            $date = $res->getGenResFromDate();

            $outString = "Reservation " . $res->getCASId();
            $outString .= ": Usuario - ".$userId;
            $outString .= ", Casa - ".$ownershipId;
            $outString .= ", Estado - ".$res->getGenResStatus();
            $outString .= ", Resultado - ".($result ? "SI" : "NO");

            $output->writeln($outString);
        }

        $output->writeln("2. Testing shallSendOutReminderEmail...");

        foreach ($reservations as $res) {
            $result = $em->getRepository("mycpBundle:generalReservation")->shallSendOutReminderEmail($res);
            $userId = $res->getGenResUserId()->getUserId();

            $payedReservations = count($em->getRepository("mycpBundle:generalReservation")->getPayedReservations($userId));

            $outString = "Reservation " . $res->getCASId();
            $outString .= ": Usuario - ".$userId;
            $outString .= ", Previas - ".$payedReservations;
            $outString .= ", Estado - ".$res->getGenResStatus();
            $outString .= ", Resultado - ".($result ? "SI" : "NO");

            $output->writeln($outString);
        }*/



        $output->writeln("End of testings");
    }

}