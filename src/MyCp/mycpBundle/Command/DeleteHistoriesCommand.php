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
 * This command must run weekly
 */

class DeleteHistoriesCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp_task:delete_histories')
                ->setDefinition(array())
                ->setDescription('Delete user histories everyday');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $output->writeln(date(DATE_W3C) . ': Starting deleting command...');

        $userHistories = $em->getRepository('mycpBundle:userHistory')->findAll();

        if (empty($userHistories)) {
            $output->writeln('No histories found for delete.');
            return 0;
        }

        $output->writeln('Deleting: ' . count($userHistories));

        try
        {
            foreach($userHistories as $uHistory)
            {
                $em->remove($uHistory);
            }

            $em->flush();

        } catch (\Exception $e) {
            $message = "Could not send Email" . PHP_EOL . $e->getMessage();
            $output->writeln($message);
        }

        $output->writeln('Operation completed!!!');
        return 0;
    }

}
