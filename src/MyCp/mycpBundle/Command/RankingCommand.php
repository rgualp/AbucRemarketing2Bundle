<?php

namespace MyCp\mycpBundle\Command;

use Doctrine\ORM\Query\ResultSetMapping;
use MyCp\mycpBundle\Entity\ownershipStatus;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Entity\mailList;

/*
 * This command must run weekly
 */

class RankingCommand extends ContainerAwareCommand {

    private $em;
    private $container;
    private $notification_email;

    protected function configure() {
        $this
                ->setName('mycp:calculate_ranking')
                ->setDefinition(array())
                ->setDescription('Calculate ranking monthly')
                ->addArgument("month", InputArgument::OPTIONAL,"Month to calculate ranking")
                ->addArgument("year", InputArgument::OPTIONAL,"Year to calculate ranking")
                ->addOption("sendEmails", null, InputOption::VALUE_NONE, 'Indicate if command send emails or not')
        ;
    }

    protected function loadConfig(){
        $this->container = $this->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
        $this->notification_email = $this->container->get('mycp.notification.mail.service');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->loadConfig();

        $output->writeln(date(DATE_W3C) . ': Starting ranking command...');
        $monthArg = intval($input->getArgument("month"));
        $yearArg = intval($input->getArgument("year"));
        $sendEmails= $input->getOption('sendEmails');

        if($monthArg == null or $monthArg == "" or $yearArg == null or $yearArg == "")
        {
            $monthArg = intval(date("m"));
            $yearArg = intval(date("Y"));
            $yearArg = ($monthArg == 1) ? $yearArg - 1: $yearArg;
            $monthArg = ($monthArg == 1) ? 12 : $monthArg - 1;
        }

        $output->writeln('Month: '.$monthArg.". Year: ".$yearArg);
        $output->writeln('Calculating ranking...');

        try {
            $qb = $this->em->createNativeQuery(
                'CALL calculateRanking (:monthValue, :yearValue)',
                new ResultSetMapping()
            );
            $qb->setParameters(
                array(
                    'monthValue' => $monthArg,
                    'yearValue' => $yearArg
                ));
            $qb->execute();
            //$em->flush();
        }
        catch(\Exception $e){
            $output->writeln('Server is crazy. Said: ' . $e->getMessage());
        }


        if($sendEmails) {
            $output->writeln('And now we are going to send emails to accommodations owners');
            $this->sendEmails($monthArg, $yearArg, $output);
        }

        $output->writeln('Oh yeah!!! Ranking is calculated!!');
        return 0;
    }

    protected function sendEmails($monthArg, $yearArg, $output)
    {
        //Buscar informacion de los alojamientos
        $accommodationsRankingValues = $this->em->getRepository("mycpBundle:ownership")->getRankingStatisticsToSendEmails($monthArg, $yearArg);

        foreach($accommodationsRankingValues as $rankingValue)
        {
            //Enviar correo
            $from_email= 'no_responder@mycasaparticular.com';
            $from_name= 'MyCasaParticular.com';
            $email_type= 'RANKING_EMAIL';
            $emailValues = array("subject" => "", "content" => "");

            if($rankingValue["ranking"] <= 0 || $rankingValue["previousRank"] == null)
            {
                $emailValues = $this->sendEmailAccommodationsWithNegativeOrZeroRanking($rankingValue);
            }
            elseif($rankingValue["place"] <= 10)
            {
                $emailValues = $this->sendEmailAccommodationsTop10($rankingValue);
            }

            elseif($rankingValue["place"] > 10 && $rankingValue["place"] <= $rankingValue["previousPlace"])
            {
                $emailValues = $this->sendEmailAccommodationsUpRanking($rankingValue);
            }
            elseif($rankingValue["place"] > 10 && $rankingValue["place"] > $rankingValue["previousPlace"])
            {
                $emailValues = $this->sendEmailAccommodationsDownRanking($rankingValue);
            }

            if($emailValues["subject"] != "" && $emailValues["content"] != "") {
                $this->notification_email->setTo(array($rankingValue["email"]));
                //$this->notification_email->setTo("yanet.moralesr@gmail.com");
                $this->notification_email->setSubject($emailValues["subject"]);
                $this->notification_email->setFrom($from_email, $from_name);
                $this->notification_email->setBody($emailValues["content"]);
                $this->notification_email->setEmailType($email_type);

                $status = $this->notification_email->sendEmail();
                if ($status) {
                    $output->writeln('<info>' . $rankingValue["email"] . '</info>');
                } else {
                    $output->writeln('<error>' . $rankingValue["email"] . '</error>');
                }
            }

            //Settear el campo visitsLastWeek en 0 para cada alojamiento
            $data = $this->em->getRepository("mycpBundle:ownershipData")->findOneBy(array("accommodation" => $rankingValue["id"]));
            if(isset($data))
            {
                $data->setVisitsLastWeek(0);
                $this->em->persist($data);
                $this->em->flush();
            }
        }

    }

    protected function sendEmailAccommodationsDownRanking($rankingValue)
    {
        $subject = "Atraviesas un difícil momento, es hora de tomar un impulso";
        $email_manager = $this->container->get('mycp.service.email_manager');
        $body = $email_manager->getViewContent('FrontEndBundle:mails:sendEmailRankingAccommodationsDownRanking.html.twig', $rankingValue);

        return array("subject" => $subject, "content" => $body);
    }

    protected function sendEmailAccommodationsUpRanking($rankingValue)
    {
        $subject = "Enhorabuena! Tu alojamiento se ha vuelto muy popular";
        $email_manager = $this->container->get('mycp.service.email_manager');
        $body = $email_manager->getViewContent('FrontEndBundle:mails:sendEmailRankingAccommodationsUpRanking.html.twig', $rankingValue);
        return array("subject" => $subject, "content" => $body);
    }

    protected function sendEmailAccommodationsTop10($rankingValue)
    {
        $subject = "WOW! Tu hospedaje sí sabe cómo colocarse en la cima";
        $email_manager = $this->container->get('mycp.service.email_manager');
        $body = $email_manager->getViewContent('FrontEndBundle:mails:sendEmailRankingAccommodationsTop10.html.twig', $rankingValue);

        return array("subject" => $subject, "content" => $body);
    }

    protected function  sendEmailAccommodationsWithNegativeOrZeroRanking($rankingValue)
    {
        $subject = "Descubre cómo tu casa puede conseguir más reservas";
        $email_manager = $this->container->get('mycp.service.email_manager');
        $body = $email_manager->getViewContent('FrontEndBundle:mails:sendEmailRankingAccommodationsWithNegativeOrZeroRanking.html.twig', $rankingValue);

        return array("subject" => $subject, "content" => $body);
    }

}
