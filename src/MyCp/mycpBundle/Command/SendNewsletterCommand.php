<?php

namespace MyCp\mycpBundle\Command;

use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\userCasa;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\generalReservation;
use Symfony\Component\Debug\Exception\FatalErrorException;


class SendNewsletterCommand extends ContainerAwareCommand
{
	private $newsletterService;

	private $container;

	protected function configure()
	{
		$this
			->setName('mycp_task:send_newsletter')
            ->setDefinition(
                array(
                    new InputOption('newsletter-code', 'code', InputOption::VALUE_REQUIRED)
                ))
            ->setDescription('Send a newsletter');

	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->loadConfig();
		$code= $input->getOption('newsletter-code');

        $output->writeln('Código: '.$code);

        if($code != "")
            $this->newsletterService->sendNewsletterByCode($code);

		$output->writeln('You rock!');
	}


	protected function loadConfig(){
        $this->container = $this->getContainer();
		$this->newsletterService = $this->container->get('mycp.newsletter.service');
	}
}
