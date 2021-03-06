<?php

namespace MyCp\mycpBundle\Command;

use Abuc\RemarketingBundle\JobData\JobData;
use Abuc\RemarketingBundle\Worker\Worker;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\JobData\GeneralReservationJobData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Helpers\EmailManager;
use Doctrine\ORM\EntityManager;

/**
 * Class ReservationReminderWorkerCommand
 *
 * Worker for sending out reservation reminder emails to customers.
 *
 * @package MyCp\mycpBundle\Command
 */
class ReservationReminderWorkerCommand extends Worker
{
    /**
     * @var  OutputInterface
     */
    private $output;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * 'translator' service
     * @var
     */
    private $translatorService;

    /**
     * 'time' service
     * @var
     */
    private $timeService;

    /**
     * 'mycp.service.email_manager' service
     *
     * @var EmailManager
     */
    private $emailManager;
    private $logger;

    /**
     * {@inheritDoc}
     */
    protected function configureWorker()
    {
        $this
            ->setName('mycp:worker:reservationreminder')
            ->setDefinition(array())
            ->setDescription('Sends a reminder email after 24h if a user has not yet confirmed his reservation')
            ->setJobName('mycp.job.reservation.reminder');
    }

    /**
     * {@inheritDoc}
     */
    protected function work(JobData $data,
                            InputInterface $input,
                            OutputInterface $output)
    {
        if(!($data instanceof GeneralReservationJobData)) {
            throw new \InvalidArgumentException('Wrong datatype received: ' . get_class($data));
        }

        $this->output = $output;
        $this->initializeServices();
        $reservationId = $data->getReservationId();

        $output->writeln('Processing Reservation Reminder for Reservation ID ' . $reservationId);

        /** @var generalReservation $user */
        $generalReservation = $this->em
                ->getRepository('mycpBundle:generalReservation')
                ->getGeneralReservationById($reservationId);

        if ($this->em->getRepository('mycpBundle:generalReservation')->shallSendOutReminderEmail($generalReservation)) {

            /** @var user $user */
            $user = $generalReservation->getGenResUserId();
            $this->emailManager->setLocaleByUser($user);

            $output->writeln('Send Reservation Reminder Email to User ID ' . $user->getUserId());
            $this->sendReminderEmail($generalReservation, $user);
        }

        $output->writeln('Successfully finished Reservation Reminder for Reservation ID ' . $reservationId);

        return true;
    }

    /**
     * Sends a reservation reminder email to a user.
     *
     * @param generalReservation $generalReservation
     * @param user $user
     */
    private function sendReminderEmail(generalReservation $generalReservation, user $user)
    {
        $userId =  $user->getUserId();
        $userEmail = $user->getUserEmail();

        if($user->getUserRole() == "ROLE_CLIENT_PARTNER"||$user->getUserRole() == "ROLE_CLIENT_PARTNER_TOUROPERATOR"||$user->getUserRole() == "ROLE_ECONOMY_PARTNER")

        {
            $emailBody = $this->renderEmailBodyPartner($user, $generalReservation);
            $emailSubject = $this->translatorService->trans('REMINDER');

            $this->output->writeln("Partner: Send email to $userEmail, subject '$emailSubject' for User ID $userId");
            $this->logger->logMail(date('Y-m-d H:i:s') . " Worker ReservationReminder Email (Partner): " . $userEmail . " " . print_r($emailBody));

            $this->emailManager->sendTemplatedPartnerEmail(
                $userEmail, $emailSubject, $emailBody, 'reservation.partner@mycasaparticular.com');
        }

        else {
            $emailBody = $this->renderEmailBody($user, $generalReservation);

            $emailSubject = $this->translatorService->trans('REMINDER');

            $this->output->writeln("Send email to $userEmail, subject '$emailSubject' for User ID $userId");
            $this->logger->logMail(date('Y-m-d H:i:s') . " Worker ReservationReminder Email: " . $userEmail . " " . print_r($emailBody));

            $this->emailManager->sendEmail(
                $userEmail, $emailSubject, $emailBody, 'reservation@mycasaparticular.com');
        }
    }

    /**
     * Renders and returns the email body of the reservation reminder email.
     *
     * @param user $user
     * @param generalReservation $generalReservation
     * @return string
     */
    private function renderEmailBody(user $user, generalReservation $generalReservation)
    {
        $ownershipReservations = $this->em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        $arrayPhotos = array();
        $arrayNights = array();

        $initialPayment = 0;
        $userTourist = $this->em->getRepository("mycpBundle:userTourist")->findOneBy(array("user_tourist_user" => $user->getUserId()));

        foreach($ownershipReservations as $ownershipReservation)
        {
            $photos = $this->em
                ->getRepository('mycpBundle:ownership')
                ->getPhotos(
                    // TODO: This line is very strange. Why take the ownId of the genRes of the ownRes?!
                    $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()
                );

            if(!empty($photos)) {
                array_push($arrayPhotos, $photos);
            }

            $nights = $this->timeService
                ->nights(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(),
                    $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
                );

            array_push($arrayNights, $nights);

            $comission = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent()/100;
            //Initial down payment
            if($ownershipReservation->getOwnResNightPrice() > 0)
                $initialPayment += $ownershipReservation->getOwnResNightPrice() * $nights * $comission;
            else
                $initialPayment += $ownershipReservation->getOwnResTotalInSite() * $comission;
        }
        $user_locale = $this->emailManager->getUserLocale($user);

        $body = $this->emailManager
            ->getViewContent('FrontEndBundle:mails:reminder_available.html.twig', array(
                'user' => $user,
                'reservations' => $ownershipReservations,
                'photos' => $arrayPhotos,
                'nights' => $arrayNights,
                'user_locale' => $user_locale,
                'initial_payment' => $initialPayment,
                'user_currency' => ($userTourist != null) ? $userTourist->getUserTouristCurrency() : null
            ));

        return $body;
    }

    private function renderEmailBodyPartner(user $user, generalReservation $generalReservation)
    {
        $roomReservations = $generalReservation->getOwn_reservations();
        $user_locale = $this->emailManager->getUserLocale($user);

        $tourOperator = $this->em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();

        $nights = 0;
        $adults = 0;
        $children = 0;
        foreach($roomReservations as $roomReservation)
        {
            $nights += $this->timeService->nights($roomReservation->getOwnResReservationFromDate()->getTimestamp(), $roomReservation->getOwnResReservationToDate()->getTimestamp());
            $adults += $roomReservation->getOwnResCountAdults();
            $children += $roomReservation->getOwnResCountChildrens();
        }

        $body = $this->emailManager
            ->getViewContent('PartnerBundle:Mail:reservationReminder.html.twig', array(
                "reservation" => $generalReservation,
                "user_locale" => $user_locale,
                "agencyName" => $travelAgency->getName(),
                "nights" => ($nights / count($roomReservations)),
                "adults" => $adults,
                "children" => $children,
                "currency" => $user->getUserCurrency()
            ));

        return $body;
    }

    /**
     * Initializes the services needed.
     */
    private function initializeServices()
    {
        $this->emailManager = $this->getService('mycp.service.email_manager');
        $this->em = $this->getService('doctrine.orm.entity_manager');
        $this->timeService = $this->getService('time');
        $this->translatorService = $this->getService('translator');
        $this->logger = $this->getService('mycp.logger');
    }
}
