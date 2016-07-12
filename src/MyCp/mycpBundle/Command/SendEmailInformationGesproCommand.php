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


class SendEmailInformationGesproCommand extends ContainerAwareCommand
{
	private $em;

	private $container;

	/**
	 * @var \MyCp\mycpBundle\Service\NotificationMailService
	 */
	private $notification_email;

	protected function configure()
	{
		$this
			->setName('mycp_task:send_email_information_gespro')
			->setDescription('Send notification to user ');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->loadConfig();
		$mail_fails= array();
		$mail_success= array();

		$mails= array(
			'ander@mycasaparticular.com'
		);
//		$ownerships= $this->getOwnerships();
//		foreach ($ownerships as $ownership) {
//			$mail = trim($ownership->getOwnEmail1());
//			if (empty($mail))
//				$mail = trim($ownership->getOwnEmail2());
//
//			$mails[]= $mail;
//		}

		foreach ($mails as $mail) {

			$to= array($mail);
			$subject= 'Lo último de MyCasaParticular.com.';
			$from_email= 'no_responder@mycasaparticular.com';
			$from_name= 'MyCasaParticular.com';
			$email_type= 'NOTIFICATION_USERS_INFORMATIONS';

			$email_manager = $this->container->get('mycp.service.email_manager');
			$data= array();
			$data['user_locale']= 'es';
			$body = $email_manager->getViewContent('FrontEndBundle:mails:SendEmailGesproCommand.html.twig', $data);

			$this->notification_email->setTo($to);
			$this->notification_email->setSubject($subject);
			$this->notification_email->setFrom($from_email, $from_name);
			$this->notification_email->setBody($body);
			$this->notification_email->setEmailType($email_type);

			$status= $this->notification_email->sendEmail();
			if($status){
				$output->writeln('<info>'.$mail.'</info>');
				$mail_success[]= $mail;
			}else{
				$output->writeln('<error>'.$mail.'</error>');
				$mail_fails[]= $mail;
			}
		}

		if(count($mail_fails)){
			$output->writeln('<error>Emails que no se enviaron:</error>');
			dump($mail_fails);
		}else{
			$output->writeln('<info>Se mandaron a notificar '.count($mail_success).' usuarios...</info>');
		}
	}

	protected function loadConfig(){
		$this->container = $this->getContainer();
		$this->em = $this->container->get('doctrine')->getManager();
		$this->notification_email = $this->container->get('mycp.notification.mail.service');
	}

	protected function getOwnerships(){
		$sql= 'select o from mycpBundle:ownership o ';
		$sql.= "WHERE (o.own_email_1 !='' OR o.own_email_2 !='') ";//Asegurar que tiene email
		$sql.= "AND o.own_status = 1 ";//Status 1=Activo

		$q = $this->em->createQuery(trim($sql));
		$results= $q->execute();
		return $results;

	}

}
