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

    protected function configure() {
        $this
                ->setName('mycp:calculate_ranking')
                ->setDefinition(array())
                ->setDescription('Calculate ranking monthly')
                ->addArgument("month", InputArgument::OPTIONAL,"Month to calculate ranking")
                ->addArgument("year", InputArgument::OPTIONAL,"Year to calculate ranking")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

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

        $qb = $em->createNativeQuery(
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


        $output->writeln('Operation completed!!!');
        return 0;
    }

}
