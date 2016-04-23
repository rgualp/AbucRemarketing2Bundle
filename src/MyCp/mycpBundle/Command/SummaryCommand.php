<?php
/**
 * Created by PhpStorm.
 * User: neti
 * Date: 03/11/2015
 * Time: 15:22
 */

namespace MyCp\mycpBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SummaryCommand extends ContainerAwareCommand
{
    protected function configure() {
        $this
            ->setName('mycp:summary_report_notify')
            ->setDefinition(array())
            ->setDescription('Send summary report  to user');
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $output->writeln('Starting summary reports command...');

        $countClientSol = $em->getRepository("mycpBundle:generalReservation")->countClientSol();

        $countClientDisponibility = $em->getRepository("mycpBundle:generalReservation")->countClientDisponibility();

        $pending = $em->getRepository("mycpBundle:generalReservation")->getReservationClientByStatusYesterday(0);

        $reserved=$em->getRepository("mycpBundle:generalReservation")->getReservationClientByStatusYesterday(2);


        $countReservationYesterday=$em->getRepository("mycpBundle:generalReservation")->countReservationYesterday();

        $countReservationDispon=$em->getRepository("mycpBundle:generalReservation")->getReservationByStatusYesterday(1);

        $countReservationNoDispon=$em->getRepository("mycpBundle:generalReservation")->getReservationByStatusYesterday(3);

        $countReservationPag=$em->getRepository("mycpBundle:generalReservation")->getReservationByStatusYesterday(2);

        $emailService = $container->get('mycp.service.email_manager');
        $templatingService = $container->get('templating');
        $logger = $container->get('logger');

        //Cuerpo del correo
        $body = $templatingService
            ->renderResponse('mycpBundle:reports:emailSummary.html.twig', array(
                'countClientSol'=>$countClientSol[0][1],
                'countClientDisponibility'=>$countClientDisponibility[0][1],
                'pending'=>$pending[0][1],
                'reserved'=>$reserved[0][1],
                'countReservationYesterday'=>count($countReservationYesterday),
                'countReservationDispon'=>count($countReservationDispon),
                'countReservationNoDispon'=>count($countReservationNoDispon),
                'countReservationPag'=>count($countReservationPag),
                'fecha'=> date("Y-m-d", strtotime('-1 day')),
                'user_locale'=>'es'
            ));

        try {
            $subject = "Sumario MyCasaParticular";

            $emailService->sendEmail(array('damian.flores@mycasaparticular.com','ander@mycasaparticular.com'), $subject,  $body, 'no-responder@mycasaparticular.com');
            $output->writeln('Successfully sent sales report email to address usuario@mycasaparticular.com');


        } catch (\Exception $e) {
            $message = "Could not send Email" . PHP_EOL . $e->getMessage();
            $logger->warning($message);
            $output->writeln($message);
        }



        $output->writeln('Operation completed!!!');
        return 0;
    }
}