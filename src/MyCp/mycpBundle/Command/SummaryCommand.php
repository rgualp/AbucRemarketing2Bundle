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

        //$data = $em->getRepository("mycpBundle:ownershipReservation")->getOwnReservationsForNightsCounterTotal();


        $emailService = $container->get('mycp.service.email_manager');
        $templatingService = $container->get('templating');
        $logger = $container->get('logger');

        //Cuerpo del correo
        $body = $templatingService
            ->renderResponse('mycpBundle:mail:salesReportMail.html.twig', array(

            ));

        try {
            $emailArg = $input->getArgument("email");
            $subject = "Sumario MyCasaParticular";

            $emailService->sendEmail('usuario@mycasaparticular.com', $subject,  $body, 'no-responder@mycasaparticular.com');
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