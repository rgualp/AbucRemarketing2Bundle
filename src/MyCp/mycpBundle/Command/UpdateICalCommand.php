<?php

namespace MyCp\mycpBundle\Command;

use Abuc\RemarketingBundle\Event\JobEvent;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\taskRenta;
use MyCp\mycpBundle\Helpers\FilterHelper;
use MyCp\mycpBundle\JobData\GeneralReservationJobData;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use JMS\DiExtraBundle\Annotation as DI;

class UpdateICalCommand extends ContainerAwareCommand {

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $em;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    protected function configure() {
        $this->setName('ical:ownership:update')->setDescription('Actualizar ical de las casas');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->executeUpdateAllICal($input, $output);
    }

    private function executeUpdateAllICal(InputInterface $input, OutputInterface $output) {
        $now = new \DateTime();
        $now_format = $now->format('Y-m-d H:i:s');
        $output->writeln('<info>**** ---------------------Inicio executeUpdateAllICal:' . $now_format . '--------------------- ****</info>');

        $this->container = $this->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();

        $ownerships = $this->em->getRepository('mycpBundle:ownership')->getAll('', '1', '', '', '', '', '', '', '', '', FilterHelper::ACCOMMODATION_WITH_ICAL, '')->getResult();

        $output->writeln('<info>**** ---------------------Cantidad:' . count($ownerships) . '--------------------- ****</info>');
        foreach ( $ownerships as $ownership ){
            $output->writeln('<info>**** ---------------------Actualizando calendario de :' . $ownership['own_id'] . '--------------------- ****</info>');
            $this->execute_ical($ownership['own_id']);
        }

        $now = new \DateTime();
        $now_format = $now->format('Y-m-d H:i:s');
        $output->writeln('<info>**** -------------------------Fin executeUpdateAllICal:' . $now_format . '--------------------------- ****</info>');
    }

    public function execute_ical($id_ownership) {
        $em = $this->container->get('doctrine')->getManager();
        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);

        $calendarService = $this->container->get('mycp.service.calendar');

        $rooms = $ownership->getOwnRooms();
        foreach ( $rooms as $room ) {
            if($room->getIcal() != '' && $room->getIcal() != null){
                $calendarService->readICalOfRoom($room);
            }
        }

        return true;
    }
}