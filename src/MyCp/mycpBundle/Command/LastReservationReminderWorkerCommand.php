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
 * Class LastReservationReminderWorkerCommand
 *
 * Worker for sending out last reservation reminder emails to customers.
 *
 * @package MyCp\mycpBundle\Command
 */
class LastReservationReminderWorkerCommand extends Worker
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

    /**
     * 'router' service
     *
     * @var
     */
    private $router;

    /**
     * {@inheritDoc}
     */
    protected function configureWorker()
    {
        $this
            ->setName('mycp:worker:reservationlastreminder')
            ->setDefinition(array())
            ->setDescription('Sends a reminder email after 42h if a user has not yet confirmed his reservation')
            ->setJobName('mycp.job.reservation.last.reminder');
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

        /** @var generalReservation $reservationId */
        $generalReservation = $this->em
                ->getRepository('mycpBundle:generalReservation')
                ->getGeneralReservationById($reservationId);

        if ($this->em->getRepository('mycpBundle:generalReservation')->shallSendOutReminderEmail($generalReservation)) {

            /** @var user $user */
            $user = $generalReservation->getGenResUserId();
            $this->emailManager->setLocaleByUser($user);

            $output->writeln('Send Last Reminder Email for Reservation ID ' . $reservationId);
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
        $emailBody = $this->renderEmailBody($user, $generalReservation);

        $emailSubject = $this->translatorService->trans('LAST_CHANCE_TO_BOOK_SUBJECT');

        $this->output->writeln("Send email to $userEmail, subject '$emailSubject' for User ID $userId");

        $this->emailManager->sendEmail(
            $userEmail, $emailSubject, $emailBody, 'reservation@mycasaparticular.com');
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

        $userLocale = $this->emailManager->getUserLocale($user);
        $genResId = $generalReservation->getGenResId();
        $paymentUrl = $this->getPaymentUrl($userLocale);
        $cancelReservationUrl = $this->getCancelReservationUrl($genResId, $userLocale);

        $body = $this->emailManager
            ->getViewContent('FrontEndBundle:mails:last_reminder_available.html.twig', array(
                'user' => $user,
                'reservations' => $ownershipReservations,
                'photos' => $arrayPhotos,
                'nights' => $arrayNights,
                'paymentUrl' => $paymentUrl,
                'cancelReservationUrl' => $cancelReservationUrl,
                'user_locale' => $userLocale,
                'initial_payment' => $initialPayment,
                'generalReservationId' => $genResId,
                'user_currency' => ($userTourist != null) ? $userTourist->getUserTouristCurrency() : null
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
        $this->router = $this->getService('router');
    }

    /**
     * @param $userLocale
     * @return string
     */
    private function getPaymentUrl($userLocale)
    {
        $enableUrl = $this->router->generate('frontend_mycasatrip_available', array(
            'locale' => $userLocale,
            '_locale' => $userLocale
        ), true);
        return $enableUrl;
    }

    /**
     * @param $userLocale
     * @return string
     */
    private function getCancelReservationUrl($generalReservationId, $userLocale)
    {
        $enableUrl = $this->router->generate('frontend_mycasatrip_cancel_offer', array(
            'locale' => $userLocale,
            '_locale' => $userLocale,
            'generalReservationId' => $generalReservationId
        ), true);
        return $enableUrl;
    }
}
