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
                $payAtService = $check["to_pay_at_service"] - $check["to_pay_at_service"] * $check["own_commission_percent"] / 100;
                $reservation = array(
                    "mobile" => $check["own_mobile_number"],
                    "nights" => $check["nights"] / $check["rooms"],
                    "smsNotification" => $check["own_sms_notifications"],
                    "touristCompleteName" => $check["user_user_name"]." ".$check["user_last_name"],
                    "payAtService" => number_format((float)$payAtService, 2, '.', ''),
                    "arrivalDate" => $check["own_res_reservation_from_date"]->format("d-m-Y"),
                    "casId" => "CAS.".$check["gen_res_id"],
                    "genResId" => $check["gen_res_id"]

                );
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
