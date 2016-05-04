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

		$users= $this->getUserCasa();
		$total= count($users);
		$output->writeln('<info>'.$total.' usuarios.</info>');

//		$accounts= array();
//		foreach ($users as $user) {
//			$email= $user->getUserCasaUser()->getUserEmail();
//			$accounts[]= $email;
//		}

		#region Test
		$accounts= array();
		$accounts[]= 'arieskienmendoza@gmail.com';
		$accounts[]= 'ander@mycasaparticular.com';
		$accounts[]= 'olga.dias@hds.li';
		#endregion
		$this->service_email->notificationSmsCheckIn($accounts);
		$output->writeln('<info>Ok</info>');
	}

	protected function getUserCasa(){
		$sql= 'select uc from mycpBundle:userCasa uc ';
		$sql.= 'INNER JOIN ';
		$sql.= 'mycpBundle:user u ';
		$sql.= 'WITH u = uc.user_casa_user ';
		$sql.= 'INNER JOIN ';
		$sql.= 'mycpBundle:ownership o ';
		$sql.= 'WITH o = uc.user_casa_ownership ';
		$sql.= 'WHERE u.user_enabled = 1 ';

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
