<?php

namespace MyCp\mycpBundle\Command;

use Doctrine\ORM\Query\ResultSetMapping;
use MyCp\mycpBundle\Entity\ownershipStatus;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Entity\mailList;

/*
 * This command must run weekly
 */

class RankingCommand extends ContainerAwareCommand {

    private $em;
    private $container;
    private $notification_email;

    protected function configure() {
        $this
                ->setName('mycp:calculate_ranking')
                ->setDefinition(array())
                ->setDescription('Calculate ranking monthly')
                ->addArgument("month", InputArgument::OPTIONAL,"Month to calculate ranking")
                ->addArgument("year", InputArgument::OPTIONAL,"Year to calculate ranking")
        ;
    }

    protected function loadConfig(){
        $this->container = $this->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
        $this->notification_email = $this->container->get('mycp.notification.mail.service');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->loadConfig();

        $output->writeln(date(DATE_W3C) . ': Starting ranking command...');
        $monthArg = intval($input->getArgument("month"));
        $yearArg = intval($input->getArgument("year"));

        if($monthArg == null or $monthArg == "" or $yearArg == null or $yearArg == "")
        {
            $monthArg = intval(date("m"));
            $yearArg = intval(date("Y"));
            $yearArg = ($monthArg == 1) ? $yearArg - 1: $yearArg;
            $monthArg = ($monthArg == 1) ? 12 : $monthArg - 1;
        }

        $output->writeln('Month: '.$monthArg.". Year: ".$yearArg);
        $output->writeln('Calculating ranking...');

        try {
            $qb = $this->em->createNativeQuery(
                'CALL calculateRanking (:monthValue, :yearValue)',
                new ResultSetMapping()
            );
            $qb->setParameters(
                array(
                    'monthValue' => $monthArg,
                    'yearValue' => $yearArg
                ));
            $qb->execute();
            //$em->flush();
        }
        catch(\Exception $e){
            $output->writeln('Server is crazy. Said: ' . $e->getMessage());
        }

        $output->writeln('And now we are going to send emails to accommodations owners');
        $this->sendEmails($monthArg, $yearArg);



        $output->writeln('Oh yeah!!! Ranking is calculated!!');
        return 0;
    }

    protected function sendEmails($monthArg, $yearArg)
    {
        //Settear el campo visitsLastWeek en 0 para cada alojamiento
    }

}
