<?php

namespace MyCp\mycpBundle\Command;

use PhpImap\Mailbox;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/*
 * This command must run every night
 */

class SyncPaymentCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:sync_payment')
                ->setDefinition(array())
                ->setDescription('Sync Payment');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $bookingService = $container->get('front_end.services.booking');
        $em = $container->get('doctrine')->getManager();

        $mailbox = new Mailbox('{imap.mail.hostpoint.ch:993/imap/ssl}INBOX', 'pago@mycasaparticular.com', 'jC8Innjq', __DIR__);


        $date = date ( "d F Y", strToTime ( "2015-12-22" ) );
        //date ( "d M Y", strToTime ( "-7 days" ) );
        // dump($date); die;

        // if($sinceDate != null)
        $mailsIds = $mailbox->searchMailBox('SINCE "'.$date.'"');
        /*else
            $mailsIds = $mailbox->searchMailBox('ALL');*/


        if(!$mailsIds) {
            $output->writeln('No hay correos');
        }



        foreach($mailsIds as $mailId){
            $mail = $mailbox->getMail($mailId);

            if($mail->fromAddress == "no_reply@skrill.com"){
                $id = intval(preg_replace('/[^0-9]+/', '', $mail->subject), 10);
                $output->writeln('Booking '. $id);

                $booking = $em->getRepository("mycpBundle:booking")->find($id);
                $payment = $em->getRepository("mycpBundle:payment")->findOneBy(array("booking" => $id));

                if($payment == null && $booking != null) {
                    $payment = new payment();
                    $payment->setBooking($booking)
                        ->setCreated(new \DateTime())
                        ->setCurrency($booking->getBookingCurrency())
                        ->setCurrentCucChangeRate($booking->getBookingCurrency()->getCurrCucChange())
                        ->setModified(new \DateTime())
                        ->setPayedAmount($booking->getBookingPrepay())
                        ->setStatus(PaymentHelper::STATUS_SUCCESS);

                    $em->persist($payment);

                    $em->flush();

                    $bookingService->postProcessBookingPayment($payment);
                }
                else{
                    $output->writeln('Missing data');
                }

            }


            /**/
        }
        $output->writeln('Operation completed!!!');
        return 0;
    }

}
