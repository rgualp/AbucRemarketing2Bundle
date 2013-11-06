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
        $output->writeln('Sending emails...');
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getEntityManager();
        $gen_reservations =  $em->getRepository('mycpBundle:generalReservation')->get_reminder_available();
        $array_photos=array();
        $array_nigths=array();
        $service_time=$container->get('time');
        $service_email = $container->get('Email');
        if($gen_reservations)
            foreach($gen_reservations as $gen_reservation)
            {
                $reservations=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$gen_reservation->getGenResId()));

                foreach($reservations as $res)
                {
                    $photos=$em->getRepository('mycpBundle:ownership')->getPhotos($res->getOwnResGenResId()->getGenResOwnId()->getOwnId());
                    array_push($array_photos,$photos);
                    $array_dates= $service_time->dates_between($res->getOwnResReservationFromDate()->getTimestamp(),$res->getOwnResReservationToDate()->getTimestamp());
                    array_push($array_nigths,count($array_dates));
                }
                // Enviando mail al cliente
                $user_tourist=$em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user'=>$gen_reservation->getGenResUserId()->getUserId()));
                $locale = $this->get('translator');
                $this->get('translator')->setLocale(strtolower($user_tourist->getUserTouristLanguage()->getLangCode()));
                $body = $this->render('frontEndBundle:mails:reminder_available.html.twig', array(
                    'user'=>$gen_reservation->getGenResUserId(),
                    'reservations'=>$reservations,
                    'photos'=>$array_photos,
                    'nights'=>$array_nigths
                ));
                $subject = $locale->trans('REMINDER');
                $service_email->send_email(
                    $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com',
                    $gen_reservation->getGenResUserId()->getUserEmail(),
                    $body
                );
            }

        $output->writeln('Operation completed!!!');
    }
}