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

class SalesMailCommand extends ContainerAwareCommand
{
    protected function configure() {
        $this
            ->setName('mycp:sales_report_notify')
            ->setDefinition(array())
            ->setDescription('Send sales report  to sales manager')
            ->addArgument("email", InputArgument::OPTIONAL,"Send to certain email");
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $exporter = $container->get('mycp.service.export_to_excel');

        $output->writeln(date(DATE_W3C) . ': Starting sales reports command...');

        $directoryFile = $exporter->createExcelForSalesReportsCommand();

        $emailService = $container->get('mycp.service.email_manager');
        $templatingService = $container->get('templating');
        $logger = $container->get('logger');

        $body = $templatingService
            ->renderResponse('mycpBundle:mail:salesReportMail.html.twig', array(

            ));

        try {
            $emailArg = $input->getArgument("email");
            $subject = "Reporte de ventas MyCasaParticular";

            if ($emailArg != null || $emailArg != "") {
                $emailService->sendEmail($emailArg, $subject,  $body, 'no-responder@mycasaparticular.com', $directoryFile);
                $output->writeln('Successfully sent sales report email to address '.$emailArg);
            }
            else{
                    $emailService->sendEmail('ingrid@mycasaparticular.com', $subject,  $body, 'no-responder@mycasaparticular.com', $directoryFile);
                    $output->writeln('Successfully sent sales report email to address ingrid@mycasaparticular.com');

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