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


class InfoCasaRentaCommand extends ContainerAwareCommand
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     *  @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $em;

    /**
     * @var \MyCp\FrontEndBundle\Helpers\Email
     */
    private $service_email;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    protected function configure()
    {

        $this
            ->setName('mycp_task:info_casa_renta')
            ->setDescription('Info about CasaRenta App');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig($output);

        $this->output->writeln('<info>Extracting  users</info>');
        $results= $this->getAllUserCasa();
        $total= count($results);
        $output->writeln('<info>Extract '.$total.' users</info>');

        $index=1;
        foreach ($results as $user_casa) {
            $email= $user_casa->getUserCasaUser()->getUserEmail();
            $output->writeln('<info>'.$index++.' Creating content to: '.$email.'</info>');
            try{
                $this->service_email->sendInfoCasaRentaCommand($user_casa);
                $output->writeln('<info>Queued</info>');
            }catch (\Exception $e) {
                $error= $e->getMessage();
                $output->writeln('<error>Error with user: '.$email.'->'.$error.'</error>');
            }
        }
        $output->writeln('<info>Finish...</info>');
    }

    protected function loadConfig($output){
        $this->container = $this->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
        $this->service_email = $this->container->get('Email');
        $this->output=$output;
    }

    protected function getAllUserCasa(){
        $sql= 'select uc from mycpBundle:userCasa uc ';
        $sql.= 'INNER JOIN ';
        $sql.= 'mycpBundle:user u ';
        $sql.= 'WITH u = uc.user_casa_user ';
        $sql.= 'INNER JOIN ';
        $sql.= 'mycpBundle:ownership o ';
        $sql.= 'WITH o = uc.user_casa_ownership ';
        /***test***/
//        $sql.= "AND o.own_mcp_code = 'MYCP001'";//test email Ander...
        $sql.= "AND o.own_mcp_code in ('AR001','AR002','AR003','AR004') ";
        /***test END***/

        $q = $this->em->createQuery(trim($sql));
        $results= $q->execute();
        return $results;
    }


}
