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
        $emailService = $container->get('mycp.service.email_manager');
        $user_not_reservation= $em->getRepository('mycpBundle:user')->getUserNotReservations();
        if($user_not_reservation){
            foreach ($user_not_reservation as $client){

                $output->writeln('Send Offerts Reminder Email to User  ' . $client->getUserEmail());
                $this->sendOffertEmail($client);

            }

        }

        $this->CheckInTwoDays($input,$output,$emailService);
        $this->CheckInFiveDays($input,$output,$emailService);
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

    private function CheckInTwoDays(InputInterface $input, OutputInterface $output,$emailService) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $output->writeln(date(DATE_W3C) . ': Starting check-in 2 days command...');

        $date = new \DateTime();
        $startTimeStamp = $date->getTimestamp();
        $startTimeStamp = strtotime("+2 day", $startTimeStamp);
        $date->setTimestamp($startTimeStamp);
        $date = $date->format("d/m/Y");

        $checkInEmails = $em->getRepository("mycpBundle:generalReservation")->getCheckinsServiceEmail($date);

        $existsCheckIns = count($checkInEmails);

        if ($existsCheckIns == 0) {
            $output->writeln("No check-in  for $date found for send.");
            return 0;
        }

        $output->writeln('Check-in found: ' . $existsCheckIns);

        $emailService = $container->get('mycp.service.email_manager');
        $logger = $container->get('logger');
        $translator = $container->get('translator');

        try{
            foreach($checkInEmails as $tourist)
            {
                $locale = strtolower($tourist["lang_code"]);
                $subject = $translator->trans('EXTRA_SERVICES_SUBJECT', array(), null, $locale);
                $mail = (true) ? "orlando@hds.li" : $tourist["user_email"];
                $dest_list = $em->getRepository('mycpBundle:destination')->getAllDestinations($locale, null, null);

                $bodyExtraServices = $emailService->getViewContent('FrontEndBundle:mails:CheckinTwoDaysMail.html.twig', array(
                    'user_name' => $tourist["user_user_name"],
                    'main_destinations' => array_slice($dest_list, 0, 6),
                    'user_locale' => $locale));


                $emailService->sendEmail($mail, $subject, $bodyExtraServices, 'services@mycasaparticular.com');
                $emailService->sendEmail('orlando@hds.li', $subject, $bodyExtraServices, 'services@mycasaparticular.com');

                $output->writeln('Successfully sent notification email to address '.$tourist["user_email"]);
            }
        }
        catch (\Exception $e) {
            $message = "Could not send Email" . PHP_EOL . $e->getMessage();
            $logger->warning($message);
            $output->writeln($message);
        }

        $output->writeln('Operation completed!!!');
        return 0;
    }

    private function CheckInFiveDays(InputInterface $input, OutputInterface $output,$emailService) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $templatingService = $container->get('templating');
        $output->writeln(date(DATE_W3C) . ': Starting check-in 5 days command...');

        $date = new \DateTime();
        $startTimeStamp = $date->getTimestamp();
        $startTimeStamp = strtotime("+5 day", $startTimeStamp);
        $date->setTimestamp($startTimeStamp);
        $date = $date->format("d/m/Y");

        $checkInEmails = $em->getRepository("mycpBundle:generalReservation")->getCheckinsServiceEmail($date);

        $existsCheckIns = count($checkInEmails);

        if ($existsCheckIns == 0) {
            $output->writeln("No check-in  for $date found for send.");
            return 0;
        }

        $output->writeln('Check-in found: ' . $existsCheckIns);

        $emailService = $container->get('mycp.service.email_manager');
        $logger = $container->get('logger');
        $translator = $container->get('translator');


        try{
            foreach($checkInEmails as $tourist)
            {
                $locale = strtolower($tourist["lang_code"]);
                $subject = $translator->trans('EXTRA_SERVICES_SUBJECT', array(), null, $locale);
                $mail = (true) ? "orlando@hds.li" : $tourist["user_email"];
                $dest_list = $em->getRepository('mycpBundle:destination')->getAllDestinations($locale, null, null);

                $bodyExtraServices = $emailService->getViewContent('FrontEndBundle:mails:CheckinFiveDaysMail.html.twig', array(
                    'user_name' => $tourist["user_user_name"],
                    'main_destinations' => array_slice($dest_list, 0, 6),
                    'user_locale' => $locale));


                $emailService->sendEmail($mail, $subject, $bodyExtraServices, 'services@mycasaparticular.com');
                $emailService->sendEmail('orlando@hds.li', $subject, $bodyExtraServices, 'services@mycasaparticular.com');

                $output->writeln('Successfully sent notification email to address '.$tourist["user_email"]);
            }
        }
        catch (\Exception $e) {
            $message = "Could not send Email" . PHP_EOL . $e->getMessage();
            $logger->warning($message);
            $output->writeln($message);
        }

        $output->writeln('Operation completed!!!');
        return 0;
    }

    private function sendOffertEmail($client){
        $emailManager = $this->getService('mycp.service.email_manager');
        $translatorService = $this->getService('translator');
        $securityService = $this->getService('Secure');
        $timer = $this->getService('Time');
        $em = $this->getService('doctrine.orm.entity_manager');
        $logger = $this->getService('mycp.logger');
        $emailManager->setLocaleByUser($client);
        $userEmail = $client->getUserEmail();
        $userName = $client->getUserUserName();
        $emailSubject = $translatorService->trans('EMAIL_TWO_DAYS_TITTLE');

        $userTourist = $emailManager->getTouristByUser($client);
        $userLocale = (empty($userTourist))? strtolower($client->getUserLanguage()->getLangCode()):strtolower($userTourist->getUserTouristLanguage()->getLangCode());
        $top_20=$em->getRepository('mycpBundle:ownership')->top20($userLocale, null, $client->getUserId(), null);




        $emailBody = $emailManager->getViewContent(
            'FrontEndBundle:mails:aftertwodays.html.twig', array(
            'ownerships'=>$top_20->setMaxResults(6)->getResult(),
            'user_name' => $userName,
            'user_locale' => $userLocale,
        ));


        $logger->logMail(date('Y-m-d H:i:s') ." Worker FeedbackReminder Email: ".$userEmail." ".print_r($emailBody));
        $emailManager->sendEmail($userEmail, $emailSubject, $emailBody);
    }

}
