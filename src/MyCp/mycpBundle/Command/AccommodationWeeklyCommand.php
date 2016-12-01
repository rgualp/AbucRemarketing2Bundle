<?php

namespace MyCp\mycpBundle\Command;

use MyCp\mycpBundle\Entity\ownershipStatus;
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

class AccommodationWeeklyCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:accommodation_stats')
                ->setDefinition(array())
                ->setDescription('Send stats to homeowners weekly');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $output->writeln(date(DATE_W3C) . ': Starting accommodation stats command...');

        $ownerships = array();

        if (empty($ownerships)) {
            $output->writeln('No accommodations found for stats.');
            //return 0;
        }

        $output->writeln('Accommodations found: ' . count($ownerships));

        $emailService = $container->get('mycp.service.email_manager');
        $templatingService = $container->get('templating');
        $logger = $container->get('logger');


        foreach($ownerships as $accommodation) {
            $body = $templatingService
                ->renderResponse('FrontEndBundle:mails:rt_accommodation_notification.html.twig', array(
                    'ownerships' => $ownerships
                ));

            try {
                $subject = "";
                $emailArg = "";

                $emailService->sendEmail($emailArg, $subject, $body, 'no-reply@mycasaparticular.com');
                $output->writeln('Successfully sent notification email to address ' . $emailArg);

                //TODO: Buscar el data asociado al ownership
                $data = null;
                $data->setVisitsLastWeek(0);
                $em->persist($data);
                $em->flush();

            } catch (\Exception $e) {
                $message = "Could not send Email" . PHP_EOL . $e->getMessage();
                $logger->warning($message);
                $output->writeln($message);
            }
        }

        $output->writeln('Operation completed!!!');
        return 0;
    }

}
