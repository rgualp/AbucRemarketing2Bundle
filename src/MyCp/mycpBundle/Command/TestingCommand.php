<?php

namespace MyCp\mycpBundle\Command;

use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use MyCp\mycpBundle\Entity\generalReservation;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Helpers\Images;

class TestingCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('mycp:testings')
                ->setDefinition(array())
                ->setDescription('Tests some functionalities')
                ->setHelp(<<<EOT
                Command <info>mycp_task:testings</info> do some tests.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $this->testingStoreProcedure($em, $output, $container);

        $output->writeln("End of testings");
    }

    private function testingStoreProcedure($em,OutputInterface $output, $container){
        $userId = 0;
        $conn = $em->getConnection();
        $reservationId = 110203;
        $sth = $conn->prepare("CALL sp_expired_offer(?1, @u)");
        $sth->bindParam(1, $reservationId, \PDO::PARAM_INT);
        $sth->execute();

        $id = $sth->fetch();
        //$result = $sth->fetch();

        $output->writeln($id) ;
    }

    private function testingCheckingSMSNotifications($em,OutputInterface $output, $container)
    {
        $notificationService = $container->get("mycp.notification.service");
        $checkIns = $em->getRepository("mycpBundle:generalReservation")->getCheckins('04/04/2016');

        $check = $checkIns[2];
        $payAtService = $check["to_pay_at_service"] - $check["to_pay_at_service"] * $check["own_commission_percent"] / 100;
        $reservationObj = array(
            "mobile" => "52540669",
            "nights" => $check["nights"] / $check["rooms"],
            "smsNotification" => $check["own_sms_notifications"],
            "touristCompleteName" => $check["user_user_name"]." ".$check["user_last_name"],
            "payAtService" => number_format((float)$payAtService, 2, '.', ''),
            "arrivalDate" => $check["own_res_reservation_from_date"]->format("d-m-Y"),
            "casId" => "CAS.".$check["gen_res_id"],
            "genResId" => $check["gen_res_id"]

        );
        $notificationService->sendCheckinSMSNotification($reservationObj, true);
    }

    private function testingSMSNotifications($em,OutputInterface $output, $container)
    {
        $notificationService = $container->get("mycp.notification.service");
        $reservation = $em->getRepository("mycpBundle:generalReservation")->find(69210);
        $reservationObj = array(
            "mobile" => "52540669",
            "nights" => 2,
            "smsNotification" => 1,
            "touristCompleteName" => "Yanet Morales",
            "payAtService" => 20,
            "arrivalDate" => "31-03-2016",
            "casId" => "CAS.42939",
            "genResId" => 42939

        );
        $notificationService->sendInmediateBookingSMSNotification($reservation, true);
        //$notificationService->sendConfirmPaymentSMSNotification($reservation, true);
        //$notificationService->sendCheckinSMSNotification($reservationObj, true);
    }

    private function testingPostPaymentProcess($em,OutputInterface $output)
    {
        $container = $this->getContainer();
        $bookingService = $container->get("front_end.services.booking");

        $booking = $em->getRepository("mycpBundle:booking")->find(1360);

        $bookingService->processPaymentEmails($booking, PaymentHelper::STATUS_PENDING);

    }

    private function testingActivationUrl($em,OutputInterface $output)
    {
        $user = $em->getRepository("mycpBundle:user")->find(2086);
        $activationUrl = $this->getActivationUrl($user, "es");
        $activationUrl = str_replace(".com//", ".com/", $activationUrl);
        $output->writeln($activationUrl);
    }

    private function getActivationUrl($user, $userLocale)
    {
        $encodedString = $this->getContainer()->get("Secure")->getEncodedUserString($user);
        $enableUrl = $this->getContainer()->get("router")->generate('frontend_enable_user', array(
            'string' => $encodedString,
            'locale' => $userLocale,
            '_locale' => $userLocale
        ), true);
        return $enableUrl;
    }

}