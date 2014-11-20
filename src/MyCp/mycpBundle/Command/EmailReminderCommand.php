<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class EmailReminderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mycp_emails:reminder')
            ->setDefinition(array())
            ->setDescription('Send reminder mail reservation to users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $generalReservations = $em->getRepository('mycpBundle:generalReservation')->get_reminder_available();

        $output->writeln(date(DATE_W3C) . ': Starting reminder emails command...');

        if(empty($generalReservations)) {
            $output->writeln('No Reminder General Reservations found.');
            return 0;
        }

        $output->writeln('Reminder General Reservations found: ' . count($generalReservations));

        $arrayPhotos = array();
        $arrayNights = array();
        $timeService = $container->get('time');
        $emailService = $container->get('Email');
        $templatingService = $container->get('templating');
        $translatorService = $container->get('translator');
        $logger = $container->get('logger');

        foreach($generalReservations as $generalReservation)
        {
            $generalReservationId = $generalReservation->getGenResId();

            $reservations = $em
                ->getRepository('mycpBundle:ownershipReservation')
                ->findBy(array('own_res_gen_res_id' => $generalReservationId));

            foreach($reservations as $res)
            {
                $photos = $em
                    ->getRepository('mycpBundle:ownership')
                    ->getPhotos(
                        $res->getOwnResGenResId()->getGenResOwnId()->getOwnId()
                    );

                if(!empty($photos)) {
                    array_push($arrayPhotos,$photos);
                }

                $array_dates = $timeService
                    ->datesBetween(
                        $res->getOwnResReservationFromDate()->getTimestamp(),
                        $res->getOwnResReservationToDate()->getTimestamp()
                    );
                array_push($arrayNights, count($array_dates) - 1);
            }

            // Enviando mail al cliente
            $user_tourist = $em
                ->getRepository('mycpBundle:userTourist')
                ->findOneBy(array(
                    'user_tourist_user' => $generalReservation->getGenResUserId()->getUserId()
                ));

            $userLocale = strtolower($user_tourist->getUserTouristLanguage()->getLangCode());
            $translatorService->setLocale($userLocale);

            $body = $templatingService
                ->renderResponse('FrontEndBundle:mails:reminder_available.html.twig', array(
                    'user' => $generalReservation->getGenResUserId(),
                    'reservations' => $reservations,
                    'photos' => $arrayPhotos,
                    'nights' => $arrayNights,
                    'user_locale' => $userLocale,
                    'user_currency' => $user_tourist->getUserTouristCurrency()
                ));

            $subject = $translatorService->trans('REMINDER', array(), "messages", $userLocale);
            $emailAddress = $generalReservation->getGenResUserId()->getUserEmail();

            $output->writeln('Try sending Reminder email to ' . $emailAddress
                . ' for General Reservation ID ' . $generalReservationId);

            try {
                $emailService->sendEmail(
                    $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com',
                    $emailAddress,
                    $body
                );

                $output->writeln('Successfully sent Reminder email to ' . $emailAddress
                    . ' for General Reservation ID ' . $generalReservationId);
            } catch (\Exception $e) {
                $message = "Could not send Email to address $emailAddress" . PHP_EOL . $e->getMessage();
                $logger->warning($message);
                $output->writeln($message);
            }
        }

        $output->writeln('Operation completed!!!');
        return 0;
    }
}
