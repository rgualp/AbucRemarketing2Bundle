<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Entity\mailList;

/*
 * This command must run weekly
 */

class AccommodationNotificationCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:new_accommodation_notification')
                ->setDefinition(array())
                ->setDescription('Send notification of recently added accommodations to reservation team');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $output->writeln(date(DATE_W3C) . ': Starting notification command...');

        $ownerships = $em->getRepository('mycpBundle:ownership')->getNotSendedToReservationTeam();

        if (empty($ownerships)) {
            $output->writeln('No accommodations found for send.');
            return 0;
        }

        $output->writeln('Notification accommodations found: ' . count($ownerships));

        $emailService = $container->get('Email');
        $templatingService = $container->get('templating');
        $logger = $container->get('logger');

        $body = $templatingService
                ->renderResponse('FrontEndBundle:mails:rt_accommodation_notification.html.twig', array(
            'ownerships' => $ownerships
        ));

        try {
            $usersToSend = $em->getRepository("mycpBundle:mailListUser")->getUsersInListByCommand(mailList::LIST_NEW_ACCOMMODATION_COMMAND);

            foreach ($usersToSend as $mailListUser) {
                $mailAddress = $mailListUser->getMailListUser()->getUserEmail();
                $emailService->sendEmail(
                        "Últimos alojamientos añadidos", 'no-responder@mycasaparticular.com', 'MyCasaParticular.com', $mailAddress, $body
                );
                $output->writeln('Successfully sent notification email to address '.$mailAddress);
            }

            if (count($usersToSend) == 0) {
                $emailService->sendEmail(
                        "Últimos alojamientos añadidos", 'no-responder@mycasaparticular.com', 'MyCasaParticular.com', 'reservation@mycasaparticular.com', $body
                );
                $output->writeln('Successfully sent notification email to address reservation@mycasaparticular.com');
            }

        } catch (\Exception $e) {
            $message = "Could not send Email" . PHP_EOL . $e->getMessage();
            $logger->warning($message);
            $output->writeln($message);
        }

        /* Actualizar el campo de sended */
        $output->writeln('Updating data in database...');
        foreach ($ownerships as $entity) {
            $own = $em->getRepository("mycpBundle:ownership")->find($entity["ownId"]);
            $own->setOwnSendedToTeam(true);
            $em->persist($own);
        }
        $em->flush();


        $output->writeln('Operation completed!!!');
        return 0;
    }

}
