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
use Swift_Message;


class SendEmailVinalesCommand extends ContainerAwareCommand
{
	private $em;

	private $container;

	private $service_email;

	private $templating;

	/**
	 * @var Swift_Message
	 */
	private $message;

	private $mailer;

	protected function configure()
	{
		$this
			->setName('mycp_task:send_email_vinales')
			->setDescription('Send notification to user vinales');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->loadConfig();
//		$users= array("arieskienmendoza@gmail.com","yanet.moralesr@gmail.com","dayan3504@yahoo.es","mariagara@correodecuba.cu","marbely86@gmail.com","yerlisvv@princesa.sld.cu","sergioj@princesa.pri.sld.cu","casayolandaytomas@yahoo.es","lauren.gonzalez91@yahoo.es","casachelychavez@nauta.cu","villanelson@correodecuba.cu","villalaesquinita@gmail.com","niulvysc@yahoo.es","niulvysc@yahoo.es","oscar.jaime59@gmail.com","aylencintado@gmail.com","marticaypapito1@yahoo.es","yunicrespo1989@yahoo.es","gloria.rivera@nauta.cu","ridel326@gmail.com","dlnaveda07@gmail.com","maudeline74@nauta.cu","borisymileidi@gmail.com","yamishy@correodecuba.cu","isabel@correodecuba.cu","villacristal@yahoo.es","casamilagroyamile@gmail.com","sarairure@yahoo.com","villamoro59@yahoo.es","rosa@sum.upr.edu.cu","dianyv@princesa.pri.sld.cu","milena05@princesa.pri.sld.cu","casabernardoybelkys@correodecuba.cu","mogote@pinarte.cult.cu","hidalgo@princesa.pri.sld.cu","daynoscorpio99@correodecuba.cu","josefinayesther@hispavista.com","leonleonhdez@yahoo.es","celelosrubios@gmail.com","yulietcorrea@nauta.cu","liura@correodecuba.cu","escaladaencuba@gmail.com","yanetperez@correodecuba.cu","valleson@pinarte.cult.cu","el_balcon2005@yahoo.es","magdielymaidalys@nauta.cu","berta54@correodecuba.cu","casaelninoyalexander@correodecuba.cu","liana86@correodecuba.cu","maryuska@prvinales.co.cu","emilitin2009@yahoo.es","vinalespinar@tiscali.it","vistaalvalle.osiris@gmail.com","odalis7203@nauta.cu","casacarmeloyolga@correodecuba.cu","casajorgeyanaluisa@yahoo.es","alvarocubanito@yahoo.es","margotalfonso@yahoo.es","villalasalmendras@yahoo.es","marisel@vrect.upr.edu.cu","maritzaytato@gmail.com","meryechevarria@nauta.cu","mayrelis93@gmail.com","lola8107@nauta.cu","luismiguel_valido@yahoo.com","kevinyulkiel8100@gmail.com");
		$users= array("arieskienmendoza@gmail.com","yanet.moralesr@gmail.com");

		$subject= '¡Representante de MyCasaParticular.com en Viñales!';
		$from_email= 'no_responder@mycasaparticular.com';
		$from_name= 'MyCasaParticular.com';
		$data= array(
			'user_locale'=> 'es'
		);
		$content = $this->templating->render('FrontEndBundle:mails:sendEmailVinalesCommand.html.twig', $data);
		$this->message->addTo('ander@mycasaparticular.com');

		foreach($users as $user){
			$this->message->addBcc($user, $user);
		}

		$this->message->setSubject($subject)
			->setFrom($from_email, $from_name)
			->setBody($content, 'text/html');


		return $this->mailer->send($this->message);
	}

	protected function loadConfig(){
		$this->container = $this->getContainer();
		$this->em = $this->container->get('doctrine')->getManager();
		$this->service_email = $this->container->get('Email');
		$this->templating = $this->container->get('templating');
		$this->message =Swift_Message::newInstance();
		$this->mailer =$this->container->get('mailer');
	}
}
