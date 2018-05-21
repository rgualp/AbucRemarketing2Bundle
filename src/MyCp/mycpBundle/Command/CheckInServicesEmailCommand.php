<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Entity\mailList;

/*
 * This command must run every day
 */

class CheckInServicesEmailCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:checkin-services')
                ->setDefinition(array())
                ->setDescription('Send services email to every tourist to enter in 5 days')
                ->addOption("testing", null, InputOption::VALUE_NONE, 'Indicate if command is in testing mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $output->writeln(date(DATE_W3C) . ': Starting check-in command...');

        $date = new \DateTime();
        $startTimeStamp = $date->getTimestamp();
        $startTimeStamp = strtotime("+5 day", $startTimeStamp);
        $date->setTimestamp($startTimeStamp);
        $date = $date->format("d/m/Y");

        $checkInEmails = $em->getRepository("mycpBundle:generalReservation")->getCheckinsServiceEmail($date);

        $existsCheckIns = count($checkInEmails);

        if ($existsCheckIns == 0) {
            $output->writeln("No check-in  for $date found for send.");
            return 0;
        }

        $output->writeln('Check-in found: ' . $existsCheckIns);

        $emailService = $container->get('mycp.service.email_manager');
        $logger = $container->get('logger');
        $translator = $container->get('translator');
        $testing= $input->getOption('testing');

        try{
            foreach($checkInEmails as $tourist)
            {
                $locale = strtolower($tourist["lang_code"]);
                $subject = $translator->trans('EXTRA_SERVICES_SUBJECT', array(), null, $locale);
                $mail = ($testing) ? "yanet.moralesr@gmail.com" : $tourist["user_email"];
                $dest_list = $em->getRepository('mycpBundle:destination')->getAllDestinations($locale, null, null);

                $bodyExtraServices = $emailService->getViewContent('FrontEndBundle:mails:extraServicesMail.html.twig', array(
                    'user_name' => $tourist["user_user_name"],
                     'main_destinations' => array_slice($dest_list, 0, 6),
                    'user_locale' => $locale));


                $emailService->sendEmail($mail, $subject, $bodyExtraServices, 'services@mycasaparticular.com');

                $output->writeln('Successfully sent notification email to address '.$tourist["user_email"]);
            }
        }
        catch (\Exception $e) {
            $message = "Could not send Email" . PHP_EOL . $e->getMessage();
            $logger->warning($message);
            $output->writeln($message);
        }

        $output->writeln('Operation completed!!!');
        return 0;
    }

}
