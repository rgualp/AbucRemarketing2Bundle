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


class SendEmailInfoOffertsCommand extends ContainerAwareCommand
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
			->setName('mycp_task:send_email_offerts')
			->setDescription('Send notification to user ');


    }

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->loadConfig();
		$mail_fails= array();
		$mail_success= array();

		$mails= array();
		$clients= $this->getClients();
		foreach ($clients as $client) {
			$mail = trim($client->getUserEmail());
			if (empty($mail))
				$mail = trim($client->getUserEmail());

			$mails[]= $mail;
		}
        $mails[]='orlando@hds.li';
        $mails[]='laura@hds.li';
        $mails[]='andy@hds.li';
    
		foreach ($mails as $mail){
            $output->writeln(date(DATE_W3C) . $mail);
        }


		foreach ($mails as $mail) {

			$to= array($mail);
			$subject= 'New Offert, Reservations 15% Cheaper Until June 30th';


			$data= array();
			$data['user_locale']= 'es';

            $emailService = $this->container->get('mycp.service.email_manager');
            $templatingService = $this->container->get('templating');


            $body = $templatingService
                ->renderResponse('FrontEndBundle:mails:sendEmailInformationCommand.html.twig',$data);
            $status= $emailService->sendEmail($to, $subject,  $body, 'no-responder@mycasaparticular.com');


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

	protected function getClients(){
		$sql= 'select u from mycpBundle:user u';

		$sql.= " WHERE u.user_role ='ROLE_CLIENT_TOURIST'";//Asegurar que tiene email

		$sql.= "AND u.user_creation_date >= '2018-04-01 00:00:00' ";//Status 1=Activo


		$q = $this->em->createQuery(trim($sql));
		$results= $q->execute();
		return $results;

	}

}
