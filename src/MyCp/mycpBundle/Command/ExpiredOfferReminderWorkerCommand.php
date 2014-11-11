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

class ExpiredOfferReminderWorkerCommand extends Worker {

    /**
     * 'translator' service
     * @var
     */
    private $translatorService;

    /**
     * @var  OutputInterface
     */
    private $output;

    /**
     * 'Secure' service
     *
     * @var
     */
    private $securityService;

    /**
     * 'router' service
     *
     * @var
     */
    private $router;

    /**
     * 'mycp.service.email_manager' service
     *
     * @var \MyCp\mycpBundle\Helpers\EmailManager
     */
    private $emailManager;

    /**
     * {@inheritDoc}
     */
    protected function configureWorker() {
        $this
                ->setName('mycp:worker:expiredofferriminder')
                ->setDefinition(array())
                ->setDescription('Sends a reminder email of expired offer after 48h')
                ->setJobName('mycp.job.reservation.expired.offer.reminder');
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
        $this->output = $output;

        $output->writeln('Processing Offer Expired Reminder for Reservation ID ' . $reservationId);

        /** @var generalReservation $user */
        $generalReservation = $this->em
                ->getRepository('mycpBundle:generalReservation')
                ->getGeneralReservationById($reservationId);

        if ($this->em->getRepository('mycpBundle:generalReservation')->shallSendOutReminderEmail($generalReservation)) {
            $user = $generalReservation->getGenResUserId();
            $this->emailManager->setLocaleByUser($user);

            $output->writeln('Send Offer Expired Reminder Email for Reservation ID ' . $reservationId);
            $this->sendReminderEmail($generalReservation,$user);
        }

        $output->writeln('Successfully finished Offer Expired Reminder for Reservation ID ' . $reservationId);
        return true;
    }

    /**
     * Sends an account activation reminder email to a user.
     * @param $user
     * @param $output
     */
    private function sendReminderEmail(generalReservation $generalReservation,user $user) {
        $userId = $user->getUserId();
        $userEmail = $user->getUserEmail();

        $emailSubject = $this->translatorService->trans('NOT_AVAILABLE_OFFER_SUBJECT');

        $emailBody = $this->renderEmailBody($user, $generalReservation);

        $this->output->writeln("Send email to $userEmail, subject '$emailSubject' for User ID $userId");
        $this->emailManager->sendTemplatedEmail($userEmail, $emailSubject, $emailBody, 'reservation@mycasaparticular.com');
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

        //$initialPayment = 0;

        //Set generalReservation status to Not Available
        $generalReservation->setGenResStatus(generalReservation::STATUS_NOT_AVAILABLE);
        $this->em->persist($generalReservation);

        foreach($ownershipReservations as $ownershipReservation)
        {
            $ownershipReservation->setOwnResStatus(ownershipReservation::STATUS_NOT_AVAILABLE);
            $this->em->persist($ownershipReservation);

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

            /*$comission = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent()/100;
            //Initial down payment
            if($ownershipReservation->getOwnResNightPrice() > 0)
                $initialPayment += $ownershipReservation->getOwnResNightPrice() * (count($array_dates) - 1) * $comission;
            else
                $initialPayment += getOwnResTotalInSite() * $comission;*/
        }

        $this->em->flush();

        $body = $this->emailManager
            ->getViewContent('FrontEndBundle:mails:expired_offer_reminder.html.twig', array(
                'user' => $user,
                'reservations' => $ownershipReservations,
                'photos' => $arrayPhotos,
                'nights' => $arrayNights,
                'user_locale' => $this->emailManager->getUserLocale($user),
                //'initial_payment' => $initialPayment,
                //'generalReservationId' => $generalReservation->getGenResId()
            ));

        return $body;
    }

    /**
     * Initializes the services needed.
     */
    private function initializeServices() {
        $this->emailManager = $this->getService('mycp.service.email_manager');
        $this->translatorService = $this->getService('translator');
        $this->securityService = $this->getService('Secure');
        $this->router = $this->getService('router');
    }

}