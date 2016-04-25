<?php
/**
 * Created by PhpStorm.
 * User: neti
 * Date: 03/11/2015
 * Time: 15:22
 */

namespace MyCp\mycpBundle\Command;


use Sabre\VObject\Property\ICalendar\DateTime;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReservationsStatementMailCommand extends ContainerAwareCommand
{
    protected function configure() {
        $this
            ->setName('mycp:reservations_statement_notify')
            ->setDefinition(array())
            ->setDescription('Send excel to people involved')
            ->addArgument("email", InputArgument::OPTIONAL,"Send to certain email");
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $exporter = $container->get('mycp.service.export_to_excel');

        $output->writeln(date(DATE_W3C) . ': Starting reservation report mail command...');

        $directoryFile = $exporter->createExcelReservationsStatement();

        $emailService = $container->get('mycp.service.email_manager');
        $templatingService = $container->get('templating');
        $logger = $container->get('logger');

        $body = $templatingService
            ->renderResponse('mycpBundle:mail:reservationsReportMail.html.twig', array(

            ));

        try {
            $emailArg = $input->getArgument("email");
            $now=new \DateTime();
            $subject = "Parte de reservaciones MyCasaParticular: ".$now->format('d/m/Y H:m');

            if ($emailArg != null || $emailArg != "") {
                $emailService->sendEmail($emailArg, $subject,  $body, 'no-responder@mycasaparticular.com', $directoryFile);
                $output->writeln('Successfully sent reservation statement to the address '.$emailArg);
            }
            else{
                    $emailService->sendEmail(array('damian.flores@mycasaparticular.com','reservation@mycasaparticular.com', 'natalie@mycasaparticular.com'), $subject,  $body, 'no-responder@mycasaparticular.com', $directoryFile);
                    $output->writeln('Successfully sent reservation statement email to addresses');

            }

        } catch (\Exception $e) {
            $message = "Could not send Email" . PHP_EOL . $e->getMessage();
            $logger->warning($message);
            $output->writeln($message);
        }



        $output->writeln('Operation completed!!!');
        return 0;
    }
}