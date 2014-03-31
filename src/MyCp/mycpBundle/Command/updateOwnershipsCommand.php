<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Helpers\Images;

class UpdateOwnershipsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('mycp_task:update_ownerships')
                ->setDefinition(array())
                ->setDescription('Update ownerships own_rooms_total value')
                ->setHelp(<<<EOT
                Command <info>mycp_task:update_ownerships</info> update own_rooms_total value.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getEntityManager();
        $ownerships = $em->getRepository("mycpBundle:ownership")->findAll();
        $output->writeln("Updating own_rooms_total value...");
        
        foreach($ownerships as $own)
        {
            $rooms_total = count($em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $own->getOwnId())));
            $own->setOwnRoomsTotal($rooms_total);
            $em->persist($own);
        }
        
        $em->flush();
        
        $output->writeln("End of process");
    }

}