<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\generalReservation;


class DeleteCommentsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mycp_task:delete_commands')
            ->setDefinition(array())
            ->setDescription('Delete commands unpublished inserted 2 month or more ago');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Working...');
        
        $date = date ('Y-m-d');
        $newdate = strtotime ('-60 days', strtotime ($date));
        $newdate = date('Y-m-d' , $newdate);
        $output->writeln('Deleting comments with date older or equal than '.$newdate);
        
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $comments =  $em->getRepository('mycpBundle:comment')->getOldUnpublished($newdate);
        $output->writeln('Comments to delete '.count($comments));
        if($comments)
            foreach($comments as $com)
            {
               $em->remove($com);
            }
        $em->flush();

        $output->writeln('Operation completed!!!');
    }
}
