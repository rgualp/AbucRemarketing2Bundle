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

        //$mailbox = new Mailbox('{pop.mail.hostpoint.ch:110/pop3}INBOX', 'pago@mycasaparticular.com', 'jC8Innjq', __DIR__);
        $mail = imap_open('{pop.mail.hostpoint.ch:110/pop3}INBOX',
            'pago@mycasaparticular.com', 'jC8Innjq');


        $output->writeln(imap_num_recent ($mail));

        $date = date ( "d F Y", strToTime ( "2015-12-22" ) );
        //date ( "d M Y", strToTime ( "-7 days" ) );
        // dump($date); die;

        // if($sinceDate != null)
        $mailsIds = imap_search($mail, 'ALL');
        /*else
            $mailsIds = $mailbox->searchMailBox('ALL');*/


       if(!$mailsIds) {
            $output->writeln('No hay correos');
        }

        foreach($mailsIds as $mailId){
            $head = imap_rfc822_parse_headers(imap_fetchheader($mail, $mailId, FT_UID));
            $date = date('Y-m-d H:i:s', isset($head->date) ? strtotime(preg_replace('/\(.*?\)/', '', $head->date)) : time());
            $subject = $head->subject;
            $fromAddress = strtolower($head->from[0]->mailbox . '@' . $head->from[0]->host);

            $output->writeln($date);

            $output->writeln($subject);
            $output->writeln($fromAddress);

            /*if($mail->fromAddress == "no_reply@skrill.com"){
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
                    break;
                }
                else{
                    $output->writeln('Missing data');
                }

            }*/


            /**/
       }
        $output->writeln('Operation completed!!!');
        return 0;
    }

}
