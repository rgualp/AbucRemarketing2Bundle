<?php
/**
 * Created by PhpStorm.
 * User: neti
 * Date: 03/11/2015
 * Time: 15:22
 */

namespace MyCp\mycpBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SummaryCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mycp:summary_report_notify')
            ->setDefinition(array())
            ->setDescription('Send summary report  to user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $output->writeln('Starting summary reports command...');

        $countClientSol = $em->getRepository("mycpBundle:generalReservation")->countClientSol();

        $countClientDisponibility = $em->getRepository("mycpBundle:generalReservation")->countClientDisponibility();


        $pending = $em->getRepository("mycpBundle:generalReservation")->getReservationClientByStatusYesterday(0);

        $reserved = $em->getRepository("mycpBundle:generalReservation")->countReservationClientPag();

        $countReservationYesterday = $em->getRepository("mycpBundle:generalReservation")->countReservationYesterday();

        $countReservationDispon = $em->getRepository("mycpBundle:generalReservation")->getReservationByStatusYesterday(1);

        $countReservationNoDispon = $em->getRepository("mycpBundle:generalReservation")->getReservationByStatusYesterday(3);

        $countReservationPending = $em->getRepository("mycpBundle:generalReservation")->getReservationByStatusYesterday(0);

        $countReservationPag = $em->getRepository("mycpBundle:generalReservation")->countReservationPag();


        $tempCli = $countClientDisponibility[0][1] + $pending[0][1];
        if ($countClientSol[0][1] > $tempCli) {
            $clientNotDispon = $countClientSol[0][1] - ($countClientDisponibility[0][1] + $pending[0][1]);
            $clientNotDisponPercent = ($countClientSol[0][1] == 0) ? 0 : ($clientNotDispon * 100) / $countClientSol[0][1];
        } else {
            $clientNotDispon = 0;
            $clientNotDisponPercent = 0;
        }

        $emailService = $container->get('mycp.service.email_manager');
        $templatingService = $container->get('templating');
        $logger = $container->get('logger');

        /*Porcientos*/
        $countClientDisponibilityPercent = ($countClientSol[0][1] == 0) ? 0 : ($countClientDisponibility[0][1] * 100) / $countClientSol[0][1];
        $pendingPercent = ($countClientSol[0][1] == 0) ? 0 : ($pending[0][1] * 100) / $countClientSol[0][1];


        $yesterday = date("Y-m-d", strtotime('-1 day'));
        $day = date("Y-m-d");


        //$yesterday = "2017-07-03";
        //$day = "2017-07-04";

        $factu = $em->getRepository("mycpBundle:generalReservation")->facturacion($yesterday, $day);
        $desglose = $em->getRepository("mycpBundle:generalReservation")->desglose($yesterday, $day);
        $res_desglose = array();
        if (count($desglose)) {
            $i=0;
            foreach($desglose as $des){
                $temp = self::in_array_r($des->getCurrency()->getCurrName(), $res_desglose);
                if($temp!=-1){
                    $res_desglose[$temp]['amount']=$res_desglose[$temp]['amount']+$des->getPayedAmount();
                }
                else{
                    $res_desglose[$i]['code']=$des->getCurrency()->getCurrCode();
                    $res_desglose[$i]['currency']=$des->getCurrency()->getCurrName();
                    $res_desglose[$i]['amount']=$des->getPayedAmount();
                }
                $i++;
            }

        }
        $factura = $em->getRepository("mycpBundle:generalReservation")->facturacionNeta($yesterday, $day);


        $first_day = $this->_data_first_month_day();
        $total_factu = $em->getRepository("mycpBundle:generalReservation")->getClientsDailySummaryPaymentsFacturation($first_day, $day);
        $totalFactu = 0;
        foreach ($total_factu as $aux) {
            $totalFactu += $aux['facturacion'];
        }

        $clientPendig = $em->getRepository("mycpBundle:generalReservation")->clientPendig();

        $totalPending = 0;
        foreach ($clientPendig as $client) {
            $tmp = ($client['own_res_total_in_site'] * $client['own_commission_percent']) / 100;
            $totalPending += round($tmp);
        }
        $meta = 44000;
        //Cuerpo del correo
        $body = $templatingService
            ->renderResponse('mycpBundle:reports:emailSummary.html.twig', array(
                'countClientSol' => $countClientSol[0][1],
                'countClientDisponibility' => $countClientDisponibility[0][1],
                'countClientDisponibilityPercent' => round($countClientDisponibilityPercent),
                'pending' => $pending[0][1],
                'pendingPercent' => round($pendingPercent),
                'reserved' => $reserved[0][1],
                'countReservationYesterday' => count($countReservationYesterday),
                'countReservationDispon' => count($countReservationDispon),
                'countReservationNoDispon' => count($countReservationNoDispon),
                'countReservationPag' => count($countReservationPag),
                'fecha' => date("Y-m-d", strtotime('-1 day')),
                'clientNotDispon' => $clientNotDispon,
                'clientNotDisponPercent' => round($clientNotDisponPercent),
                'user_locale' => 'es',
                'factu' => (count($factu)) ? round($factu[0]['facturacion']) : 0,
                'countReservationPending' => count($countReservationPending),
                'totalSol' => count($countReservationDispon) + count($countReservationNoDispon) + count($countReservationPag) + count($countReservationPending),
                'clientPending' => count($clientPendig),
                'totalPending' => $totalPending,
                'totalClient' => count($clientPendig) + $reserved[0][1],
                'totalCUC' => (count($factu)) ? round($factu[0]['facturacion']) + $totalPending : $totalPending,
                'factura' => (count($factura)) ? $factura[0]['facturacion'] : 0,
                'diferencia' => $meta - $totalFactu,
                'meta' => $meta,
                'res_desglose'=>$res_desglose
            ));
        //dump($body);die;
        try {
            $subject = "Sumario MyCasaParticular";

            $emailService->sendEmail(array('ptorres@abuc.ch', 'natalie@mycasaparticular.com', 'ptorres@mycasaparticular.com', 'andy@hds.li', 'ander@mycasaparticular.com', 'jose.rafael@hds.li'), $subject, $body, 'no-responder@mycasaparticular.com');
            $output->writeln('Successfully sent sales report email');


        } catch (\Exception $e) {
            $message = "Could not send Email" . PHP_EOL . $e->getMessage();
            $logger->warning($message);
            $output->writeln($message);
        }


        $output->writeln('Operation completed!!!');
        return 0;
    }
    public function in_array_r($needle, $haystack, $strict = false) {
        foreach ($haystack as $key => $item) {
            if ($item['currency']==$needle) {
                return $key;
            }
        }
        return -1;
    }

    /** Ultimo dia de este mes **/
    function _data_last_month_day()
    {
        $month = date('m');
        $year = date('Y');
        $day = date("d", mktime(0, 0, 0, $month + 1, 0, $year));

        return date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
    }

    /** Primer dia de este mes **/
    function _data_first_month_day()
    {
        $month = date('m');
        $year = date('Y');
        return date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
    }


}