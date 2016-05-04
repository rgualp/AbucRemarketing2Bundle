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


class NotificationSMSCommand extends ContainerAwareCommand
{
	private $em;

	private $container;

	private $service_email;

	protected function configure()
	{
		$this
			->setName('mycp_task:notification:sms_checkin')
			->setDefinition(array())
			->setDescription('Send notification');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->loadConfig();

		$ownerships= $this->getUserCasa();
		$total= count($ownerships);
		$output->writeln('<info>'.$total.' usuarios.</info>');

		$accounts= array();
		foreach ($ownerships as $ownership) {
			$email = trim($ownership->getOwnEmail1());
			if (empty($email))
				$email = trim($ownership->getOwnEmail2());
			$accounts[]= $email;
		}
		#region Test
		$accounts[]= 'arieskienmendoza@gmail.com';
		$accounts[]= 'ander@mycasaparticular.com';
		$accounts[]= 'olga.dias@hds.li';
		#endregion
		$this->service_email->notificationSmsCheckIn($accounts);
		$output->writeln('<info>Ok</info>');
	}

	protected function getUserCasa(){
		$sql= 'select o from mycpBundle:ownership o ';
		$sql.= "WHERE (o.own_email_1 !='' OR o.own_email_2 !='') ";//Asegurar que tiene email
		$sql.= "AND o.own_status = 1";//Status 1=Activo

		$q = $this->em->createQuery(trim($sql));
		$results= $q->execute();
		return $results;
	}

	protected function loadConfig(){
		$this->container = $this->getContainer();
		$this->em = $this->container->get('doctrine')->getManager();
		$this->service_email = $this->container->get('Email');
	}
}
