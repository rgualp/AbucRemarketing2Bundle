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

        $output->writeln('Successfully finished Feedback Reminder for User ID ' . $user->getUserId());
        return true;
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
        $userLocale = strtolower($userTourist->getUserTouristLanguage()->getLangCode());

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

}
