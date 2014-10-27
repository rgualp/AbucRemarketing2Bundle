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

        /** @var generalReservation $user */
        $generalReservation = $this->getGeneralReservationById($reservationId);

        if ($this->shallSendOutReminderEmail($generalReservation)) {

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
     * Returns whether or not a reminder email should be sent out.
     *
     * @param generalReservation $generalReservation
     * @return bool
     */
    private function shallSendOutReminderEmail(generalReservation $generalReservation)
    {
        if (!$generalReservation->hasStatusAvailable()) {
            return false;
        }

        $ownershipReservations = $this->getOwnershipReservations($generalReservation);
        $isAtLeastOneOwnResAvailable = false;

        /** @var $ownershipReservation ownershipReservation */
        foreach ($ownershipReservations as $ownershipReservation) {

            if ($ownershipReservation->hasStatusAvailable()) {
                $isAtLeastOneOwnResAvailable = true;
                break;
            }
        }

        return $isAtLeastOneOwnResAvailable;
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

        $emailSubject = $this->translatorService->trans('REMINDER');

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
        $ownershipReservations = $this->getOwnershipReservations($generalReservation);

        $arrayPhotos = array();
        $arrayNights = array();

        $initialPayment = 0;

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

            $array_dates = $this->timeService
                ->datesBetween(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(),
                    $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
                );

            array_push($arrayNights, count($array_dates) - 1);

            $comission = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent()/100;
            //Initial down payment
            if($ownershipReservation->getOwnResNightPrice() > 0)
                $initialPayment += $ownershipReservation->getOwnResNightPrice() * (count($array_dates) - 1) * $comission;
            else
                $initialPayment += getOwnResTotalInSite() * $comission;
        }

        $body = $this->emailManager
            ->getViewContent('FrontEndBundle:mails:last_reminder_available.html.twig', array(
                'user' => $user,
                'reservations' => $ownershipReservations,
                'photos' => $arrayPhotos,
                'nights' => $arrayNights,
                'user_locale' => $this->emailManager->getUserLocale($user),
                'initial_payment' => $initialPayment,
                'generalReservationId' => $generalReservation->getGenResId()
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
    }

    /**
     * @param $reservationId
     * @return generalReservation
     * @throws \LogicException
     */
    private function getGeneralReservationById($reservationId)
    {
        $generalReservation = $this->em
            ->getRepository('mycpBundle:generalReservation')
            ->find($reservationId);

        if (empty($generalReservation)) {
            throw new \LogicException('No user found for ID ' . $reservationId);
        }

        return $generalReservation;
    }

    /**
     *
     * @param generalReservation $generalReservation
     * @return array An array of ownershipReservations
     */
    private function getOwnershipReservations(generalReservation $generalReservation)
    {
        $ownershipReservations = $this->em
            ->getRepository('mycpBundle:ownershipReservation')
            ->findBy(array('own_res_gen_res_id' => $generalReservation->getGenResId()));

        return $ownershipReservations;
    }
}
