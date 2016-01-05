<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Entity\mailList;


class PostPaymentProcessCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:post_payment_process')
                ->setDefinition(array())
                ->setDescription('Execute after a period of a user made a successful payment');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $output->writeln(date(DATE_W3C) . ': Starting post_payment_process command...');

        $payments = $em->getRepository("mycpBundle:payment")->findBy(array("processed" => false));

        if (count($payments) == 0) {
            $output->writeln("No payment for process");
            return 0;
        }

        $output->writeln('Payments found: ' . count($payments));

        $bookingService = $container->get('front_end.services.booking');

        foreach($payments as $payment){

            $output->writeln('Processing booking: ' . $payment->getBooking()->getBookingId());

            //Uncomment following line
            //$bookingService->postProcessBookingPayment($payment);

            $payment->setProcessed(true);
            $em->persist($payment);
            $em->flush();
        }

        $output->writeln('Operation completed!!!');
        return 0;
    }

}
