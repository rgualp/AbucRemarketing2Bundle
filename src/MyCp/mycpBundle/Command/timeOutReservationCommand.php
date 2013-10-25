<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


class timeOutReservationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mycp_task:reservation_time_out')
            ->setDefinition(array())
            ->setDescription('Put reservations not available over 48 hours');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Working...');
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getEntityManager();
        $gen_reservations =  $em->getRepository('mycpBundle:generalReservation')->get_time_over_reservations();
        if($gen_reservations)
            foreach($gen_reservations as $gen_reservation)
            {
                $gen_reservation->setGenResStatus(3);
                $em->persist($gen_reservation);
                $reservations=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$gen_reservation->getGenResId()));
                foreach($reservations as $res)
                {
                    $res->setOwnResStatus(3);
                    $em->persist($res);
                }
            }
        $em->flush();

        $output->writeln('Operation completed!!!');
    }
}