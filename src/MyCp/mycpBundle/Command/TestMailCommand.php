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


class TestMailCommand extends ContainerAwareCommand
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
			->setName('mycp_task:test_email')
			->setDefinition(array());
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->loadConfig();
		$mail_fails= array();
		$mail_success= array();
        $container = $this->getContainer();
        $emailService = $container->get('mycp.service.email_manager');
        $subject = "Reporte de ventas MyCasaParticular PRUEBAs";
        $templatingService = $container->get('templating');
        $body = $templatingService
            ->renderResponse('mycpBundle:mail:salesReportMail.html.twig', array(

            ));
        $emailService->sendEmail('orlando@hds.li', $subject,  $body, 'no-responder@mycasaparticular.com', '');


        $mails= array(
			'vhagar91@gmail.com',
            'orlando@hds.li',
			'ander@mycasaparticular.com',
			'arieskiemendoza@gmail.com',
            "anne.schweizer@abuc.ch",
		);
		foreach ($mails as $mail) {

			$to= array($mail);
			$subject= 'MyCasaParticular.com brinda nuevas oportunidades para sus clientes.';
			$from_email= "no_responder@mycasaparticular.com";
			$from_name= "MyCasaParticular.com";
			$email_type= 'Test';

			$email_manager = $this->container->get('mycp.service.email_manager');
            $container = $this->getContainer();
            $em = $container->get('doctrine')->getManager();
			$data= array();
			$data['user_locale']= 'es';
            $data['user']= $em->getRepository("mycpBundle:user")->find(18168);
			$body = $email_manager->getViewContent('FrontEndBundle:mails:boletinTest.html.twig', $data);

			$this->notification_email->setTo($to);

			$this->notification_email->setSubject($subject);
			$this->notification_email->setFrom($from_email, $from_name);
			$this->notification_email->setBody($body);
			$this->notification_email->setEmailType($email_type);

            $status= $this->notification_email->sendEmail();
			if($status){
				$output->writeln('<info>'.$mail. $status.'</info>');
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
}
