<?php

namespace MyCp\mycpBundle\Command;

use PhpImap\Mailbox;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/*
 * This command must run every night
 */

class GetStatisticsFromEmailCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:stats_fromEmail')
                ->setDefinition(array())
                ->addArgument("year", InputArgument::REQUIRED, "Año para recoger estadísticas")
                ->setDescription('Get stats from Email');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $year = $input->getArgument("year");

        try {
            $server = "{imap.mail.hostpoint.ch}INBOX.Emails_".$year;
            $connection = imap_open($server,'reservation@mycasaparticular.com', 'kkappukkon300');
            $this->procReservations($connection, $output);
            $this->procPayments($connection, $output);
            $output->writeln('Operation completed!!!');
            return 0;
        }
        catch(\Exception $e) {
            $output->writeln($e->getMessage());
        }

    }

    private function procReservations($connection, OutputInterface $output)
    {
        $mails   = imap_search($connection, 'SUBJECT "MyCasaParticular Reservas" FROM "no.reply@mycasaparticular.com"', SE_UID);
        foreach($mails as $mail){
            $output->writeln($mail);
            $message = imap_fetchbody($connection,$mail, "1", FT_UID);
            $output->writeln($message);
        }
    }

    private function procPayments($connection, OutputInterface $output)
    {
        $mails   = imap_search($connection, 'SUBJECT "Confirmación de pago. MyCasaParticular.com" FROM "no-reply@mycasaparticular.com"', SE_UID);
        foreach($mails as $mail){
            $output->writeln($mail);
            $message = imap_fetchbody($connection,$mail, "1", FT_UID);
            $output->writeln($message);
        }
    }

}
