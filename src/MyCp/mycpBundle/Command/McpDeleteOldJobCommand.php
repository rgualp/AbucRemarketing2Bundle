<?php

namespace MyCp\mycpBundle\Command;

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

class McpDeleteOldJobCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:delete_old_job')
                ->setDefinition(array())
                ->setDescription('Delete Old JOB in not uses');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $date= new \DateTime();
        $date=$date->modify("-3 day");
        $output->writeln('Processing old jobs...');
        $repository = $em->getRepository('AbucRemarketingBundle:Job');
        $repository->createQueryBuilder('r')
                            ->delete()
                            ->where('r.processDate < :date')
                            ->setParameter('date', $date)
                            ->getQuery()
                            ->execute();

        $output->writeln('Operation completed!!!');
        return 0;
    }

}
