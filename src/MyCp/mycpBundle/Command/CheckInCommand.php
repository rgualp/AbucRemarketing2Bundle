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
 * This command must run every day
 */

class CheckInCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:checkin')
                ->setDefinition(array())
                ->setDescription('Send a dayly email to reservation team with the check-ins');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $output->writeln(date(DATE_W3C) . ': Starting check-in command...');

        $date = new \DateTime();
        $startTimeStamp = $date->getTimestamp();
        $startTimeStamp = strtotime("+5 day", $startTimeStamp);
        $date->setTimestamp($startTimeStamp);
        $date = $date->format("d/m/Y");

        $checkIns = $em->getRepository("mycpBundle:generalReservation")->getCheckins($date);

        $existsCheckIns = count($checkIns);

        if ($existsCheckIns == 0) {
            $output->writeln("No check-in  for $date found for send.");
            return 0;
        }

        $output->writeln('Check-in found: ' . $existsCheckIns);

        $emailService = $container->get('mycp.service.email_manager');
        $excelService = $container->get('mycp.service.export_to_excel');
        $notificationService = $container->get('mycp.notification.service');
        $logger = $container->get('logger');
        $excelFilePath = $excelService->createCheckinExcel($date, false);

        try{
            foreach($checkIns as $check)
            {
                $reservation = $em->getRepository("mycpBundle:generalReservation")->find($check[0]["gen_res_id"]);
                $notificationService->sendCheckinSMSNotification($reservation);
            }
            $output->writeln('Successfully sent SMS notifications to homeowners.');
        }
        catch (\Exception $e) {
            $message = "Could not send SMS" . PHP_EOL . $e->getMessage();
            $logger->warning($message);
            $output->writeln($message);
        }

        try {
            $emailService->sendEmail('reservation@mycasaparticular.com',"Check-in $date","",null,$excelFilePath);
            $output->writeln('Successfully sent notification email to address reservation@mycasaparticular.com');

        } catch (\Exception $e) {
            $message = "Could not send Email" . PHP_EOL . $e->getMessage();
            $logger->warning($message);
            $output->writeln($message);
        }



        $output->writeln('Operation completed!!!');
        return 0;
    }

}
