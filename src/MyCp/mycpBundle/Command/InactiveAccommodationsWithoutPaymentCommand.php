<?php

namespace MyCp\mycpBundle\Command;

use MyCp\mycpBundle\Entity\ownershipStatistics;
use MyCp\mycpBundle\Helpers\OwnershipStatuses;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/*
 * This command must run daily
 */

class InactiveAccommodationsWithoutPaymentCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:inactive_accommodations')
                ->setDefinition(array())
                ->setDescription('Inactive accommodations without payment');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $output->writeln(date(DATE_W3C) . ': Starting searching accommodation without inscription payment...');

        $ownerships = $em->getRepository('mycpBundle:ownershipPayment')->accommodationsNoInscriptionPayment(true);

        if (empty($ownerships)) {
            $output->writeln('No accommodations found without inscription payment.');
            return 0;
        }

        $output->writeln('Accommodations without inscription payment found: ' . count($ownerships));

        try {

            foreach($ownerships as $accommodation)
            {
                $output->writeln('Processing: ' . $accommodation->getOwnMcpCode());
                $status = $em->getRepository("mycpBundle:ownershipStatus")->find(OwnershipStatuses::INACTIVE);
                $accommodation->setOwnStatus($status)
                    ->setOwnLastUpdate(new \DateTime());
                $em->persist($accommodation);

                //Insertar un ownershipStatistics
                $statistic = new ownershipStatistics();
                $statistic->setAccommodation($accommodation)
                    ->setCreated(false)
                    ->setStatus($status)
                    ->setUser(null)
                    ->setNotes("Modify by command")
                ;

                $em->persist($statistic);
            }

            $em->flush();


        } catch (\Exception $e) {
            $message = "Could not inactive accommodations " . PHP_EOL . $e->getMessage();
            $output->writeln($message);
        }

        $output->writeln('Operation completed!!!');
        return 0;
    }

}
