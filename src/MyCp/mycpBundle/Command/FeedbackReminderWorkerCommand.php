<?php

namespace MyCp\mycpBundle\Command;

use Abuc\RemarketingBundle\Job\PredefinedJobs;
use MyCp\mycpBundle\JobData\GeneralReservationJobData;
use Abuc\RemarketingBundle\Worker\Worker;
use Abuc\RemarketingBundle\JobData\JobData;
use MyCp\mycpBundle\Entity\user;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MyCp\mycpBundle\Entity\generalReservation;

class FeedbackReminderWorkerCommand extends Worker {

    /**
     * 'translator' service
     * @var
     */
    private $translatorService;

    /**
     * 'Secure' service
     *
     * @var
     */
    private $securityService;

    /**
     * 'time' service
     *
     * @var
     */
    private $timer;

    /**
     * 'mycp.service.email_manager' service
     *
     * @var \MyCp\mycpBundle\Helpers\EmailManager
     */
    private $emailManager;
    private $em;

    /**
     * 'Logger' logger
     *
     * @var
     */
    private $logger;

    /**
     * {@inheritDoc}
     */
    protected function configureWorker() {
        $this
                ->setName('mycp:worker:feedbackreminder')
                ->setDefinition(array())
                ->setDescription('Sends a reminder email after user returned from Cuba')
                ->setJobName("mycp.job.feedback.reminder");
    }

    /**
     * {@inheritDoc}
     */
    protected function work(JobData $data, InputInterface $input, OutputInterface $output) {
        if (!($data instanceof GeneralReservationJobData)) {
            throw new \InvalidArgumentException('Wrong datatype received: ' . get_class($data));
        }

        $this->initializeServices();
        $reservationId = $data->getReservationId();
        $generalReservation = $this->em
                ->getRepository('mycpBundle:generalReservation')
                ->getGeneralReservationById($reservationId);

        $user = $generalReservation->getGenResUserId();

        $output->writeln('Processing Feedback Reminder for User ID ' . $user->getUserId());

        if (empty($user)) {
            // the user does not exist anymore
            return true;
        }

        $this->emailManager->setLocaleByUser($user);

        if ($this->em->getRepository('mycpBundle:generalReservation')->shallSendOutFeedbackReminderEmail($generalReservation)) {
            $output->writeln('Send Feedback Reminder Email to User ID ' . $user->getUserId());
            $this->sendReminderEmail($user, $generalReservation, $output);
        }

        $this->CheckInTwoDays($input,$output);
        $this->CheckInFiveDays($input,$output);

        $user_not_reservation= $this->em->getRepository('mycpBundle:user')->getUserNotReservations();
        if($user_not_reservation){
            foreach ($user_not_reservation as $client){
                $this->emailManager->setLocaleByUser($client);
                $output->writeln('Send Offerts Reminder Email to User  ' . $client->getUserEmail());
                $this->sendOffertEmail($client);

            }

        }

        $output->writeln('Successfully finished Feedback Reminder for User ID ' . $user->getUserId());
        return true;
    }

    private function sendOffertEmail($client){
        $userEmail = $client->getUserEmail();
        $userName = $client->getUserUserName();
        $emailSubject = $this->translatorService->trans('EMAIL_TWO_DAYS_TITTLE');

        $userTourist = $this->emailManager->getTouristByUser($client);
        $userLocale = (empty($userTourist))? strtolower($client->getUserLanguage()->getLangCode()):strtolower($userTourist->getUserTouristLanguage()->getLangCode());
        $top_20=$this->em->getRepository('mycpBundle:ownership')->top20($userLocale, null, $client->getUserId(), null);




        $emailBody = $this->emailManager->getViewContent(
            'FrontEndBundle:mails:offerts:aftertwodays.html.twig', array(
            'ownerships'=>$top_20->setMaxResults(6)->getResult(),
            'user_name' => $userName,
            'user_locale' => $userLocale,
        ));


        $this->logger->logMail(date('Y-m-d H:i:s') ." Worker FeedbackReminder Email: ".$userEmail." ".print_r($emailBody));
        $this->emailManager->sendEmail($userEmail, $emailSubject, $emailBody);
    }
    /**
     * Sends an account activation reminder email to a user.
     * @param $user
     * @param $output
     */
    private function sendReminderEmail(user $user, generalReservation $generalReservation, OutputInterface $output) {
        $userEmail = $user->getUserEmail();
        $userName = $user->getUserUserName();

        $emailSubject = $this->translatorService->trans('FEEDBACK_REMINDER');

        $userTourist = $this->emailManager->getTouristByUser($user);
        $userLocale = (empty($userTourist))? strtolower($user->getUserLanguage()->getLangCode()):strtolower($userTourist->getUserTouristLanguage()->getLangCode());

        $ownershipReservations = $this->em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        $arrayPhotos = array();
        $arrayNights = array();

        $photos = $this->em
                ->getRepository('mycpBundle:ownership')
                ->getPhotos($generalReservation->getGenResOwnId()->getOwnId());

        if (!empty($photos)) {
            array_push($arrayPhotos, $photos);
        }

        foreach ($ownershipReservations as $ownershipReservation) {
            $nights = $this->timer
                    ->nights(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(), $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
            );

            array_push($arrayNights, $nights);
        }

        $emailBody = $this->emailManager->getViewContent(
                'FrontEndBundle:mails:feedback.html.twig', array(
                'reservations' => $ownershipReservations,
                'photos' => $arrayPhotos,
                'nights' => $arrayNights,
                'generalReservationId' => $generalReservation->getGenResId(),
                'user_name' => $userName,
                'user_locale' => $userLocale,
            ));

        $output->writeln("Send email to $userEmail, subject '$emailSubject'");
        $this->logger->logMail(date('Y-m-d H:i:s') ." Worker FeedbackReminder Email: ".$userEmail." ".print_r($emailBody));
        $this->emailManager->sendEmail($userEmail, $emailSubject, $emailBody);
    }

    /**
     * Initializes the services needed.
     */
    private function initializeServices() {
        $this->emailManager = $this->getService('mycp.service.email_manager');
        $this->translatorService = $this->getService('translator');
        $this->securityService = $this->getService('Secure');
        $this->timer = $this->getService('Time');
        $this->em = $this->getService('doctrine.orm.entity_manager');
        $this->logger = $this->getService('mycp.logger');

        //if ($this->em->getConnection()->ping() === false) {
        //    $this->em->getConnection()->close();
        //    $this->em->getConnection()->connect();
       // }
    }

    private function CheckInTwoDays(InputInterface $input, OutputInterface $output) {
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

                $bodyExtraServices = $emailService->getViewContent('FrontEndBundle:mails:offerts:CheckinTwoDaysMail.html.twig', array(
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

    private function CheckInFiveDays(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

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

                $bodyExtraServices = $emailService->getViewContent('FrontEndBundle:mails:offerts:CheckinFiveDaysMail.html.twig', array(
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
}
