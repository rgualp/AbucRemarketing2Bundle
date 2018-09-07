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


class ChangeHomesComisionCommand extends ContainerAwareCommand
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
			->setName('mycp_change_comision')
			->setDescription('Change Comisions')
            ->addArgument("comicion", InputArgument::OPTIONAL,"Comision a annadir");


    }

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->loadConfig();

        $comision = intval($input->getArgument("comicion"));


		$ownerships= $this->getOwnerships();
		foreach ($ownerships as $ownership) {

		    $previus_comision= $ownership->getOwnCommissionPercent();
		    $ownership->setOwnCommissionPercent($comision);
            $output->writeln('<error>'.$ownership->getOwnMcpCode().'--Cambiando Comision de '.$previus_comision.' a '.$comision. '</error>');
            $rooms = $this->em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $ownership->getOwnId()));
			foreach ($rooms as $room){
			    $real_price_up_to= $room->getRoomPriceUpTo()-($room->getRoomPriceUpTo()*($previus_comision/100)) ;
                $real_price_up_from=$room->getRoomPriceUpFrom()-($room->getRoomPriceUpFrom()*($previus_comision/100));
                $real_price_down_to= $room->getRoomPriceDownTo()-($room->getRoomPriceDownTo()*($previus_comision/100));
                $real_price_down_from  =$room->getRoomPriceDownFrom()-($room->getRoomPriceDownFrom()*($previus_comision/100));

                $room->setRoomPriceUpTo(round(($real_price_up_to/(1-$comision/100)),2));
                $room->setRoomPriceUpFrom(round(($real_price_up_from/(1-$comision/100)),2));
                $room->setRoomPriceDownTo(round(($real_price_down_to/(1-$comision/100)),2));
                $room->setRoomPriceDownFrom(round(($real_price_down_from/(1-$comision/100)),2));
                $this->em->persist($room);

            }
            $this->em->persist($ownership);
		}
		$this->em->flush();

	}

	protected function loadConfig(){
		$this->container = $this->getContainer();
		$this->em = $this->container->get('doctrine')->getManager();
		$this->notification_email = $this->container->get('mycp.notification.mail.service');
	}

	protected function getOwnerships(){
		$sql= 'select o from mycpBundle:ownership o';

		$sql.= " WHERE o.own_mcp_code > 10 ";//Asegurar que tiene email



        $sql.=" order by o.own_id";
		$q = $this->em->createQuery(trim($sql));
		$results= $q->execute();
		return $results;

	}

}
