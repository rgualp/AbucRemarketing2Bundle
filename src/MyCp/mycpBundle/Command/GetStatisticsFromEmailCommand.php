<?php

namespace MyCp\mycpBundle\Command;

use MyCp\mycpBundle\Entity\oldPayment;
use MyCp\mycpBundle\Entity\oldReservation;
use PhpImap\Mailbox;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/*
 * This command must run every night
 */

class GetStatisticsFromEmailCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:stats_fromEmail')
                ->setDefinition(array())
                ->addArgument("year", InputArgument::REQUIRED, "Año para recoger estadísticas")
                ->setDescription('Get stats from Email');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $year = $input->getArgument("year");

        try {
            $server = "{imap.mail.hostpoint.ch}INBOX.Emails_".$year;
            $connection = imap_open($server,'reservation@mycasaparticular.com', 'kkappukkon300');
            $this->processEmail($connection, 'FROM "no.reply@mycasaparticular.com"', true, $output, $year); //SUBJECT "MyCasaParticular Reservas"
            $this->processEmail($connection, 'FROM "no-reply@mycasaparticular.com"', false, $output, $year); //SUBJECT "Confirmación de pago. MyCasaParticular.com"
            $output->writeln('Operation completed!!!');
            return 0;

        }
        catch(\Exception $e) {
            $output->writeln($e->getMessage());
        }

    }

    private function processEmail($connection, $searchPattern, $isReservationEmail, OutputInterface $output, $year)
    {
        $mails   = imap_search($connection, $searchPattern, SE_UID);
        foreach($mails as $mail){
            $head = imap_rfc822_parse_headers(imap_fetchheader($connection, $mail, FT_UID));
            $messageRefId = $year."-".$mail;
            $output->writeln(($isReservationEmail ? "Reservas: " : "Pagos: ").$messageRefId);
            $date = date('Y-m-d H:i:s', isset($head->date) ? strtotime(preg_replace('/\(.*?\)/', '', $head->date)) : time());
            $message = imap_fetchbody($connection,$mail, "1", FT_UID);

            preg_match('#MyCasaParticular Reservas([-\w]*)#i', $head->subject, $matchesReservation);
            preg_match('#Confirmaci([\s\S]+)n de pago. MyCasaParticular.com#i', $head->subject, $matchesPayment);

            if(count($matchesReservation) || count($matchesPayment)){
                $this->processMessageBody($message, $date, $isReservationEmail, $output, $messageRefId);
            }
            else{
                $output->writeln($head->subject);
            }

        }
    }

    private function processMessageBody($messageBody, $sentDate, $isReservationEmail, OutputInterface $output, $messageRefId)
    {
        if($isReservationEmail)
            $this->createOldReservation($messageBody, $sentDate, $output, $messageRefId);
        else
            $this->createOldPayment($messageBody, $sentDate, $output, $messageRefId);
    }

    private function createOldReservation($messageBody, $sentDate, OutputInterface $output, $messageRefId)
    {
        try {
            $bodySplitted = explode("<strong>Datos de la Casa</strong><br />", $messageBody);

            $reservationSection = $bodySplitted[0];
            $accommodationSection = $bodySplitted[1];

            $container = $this->getContainer();
            $em = $container->get('doctrine')->getManager();

            preg_match('#<strong>Referencia: ([\w\d]*)</strong><br /><br />#i', $reservationSection, $matches);
            $accommodation_code = (count($matches)) ? $matches[1] : "";

            if($accommodation_code == "")
            {
                preg_match('#<strong>Referencia de reserva: ([\w\d]*)</strong><br /><br />#i', $reservationSection, $matches);
                $accommodation_code = (count($matches)) ? $matches[1] : "";
            }
            //$output->writeln($accommodation_code);

            preg_match('#<strong>ID reserva: ([\w\d\.]*)</strong><br /><br />#i', $reservationSection, $matches);
            $reservation_code = (count($matches)) ? $matches[1] : "";

            preg_match('#<li><strong>Nombre: </strong>([\w\d]*)</li>#i', $reservationSection, $matches);
            $tourist_name = (count($matches)) ? $matches[1] : "";
            //$output->writeln($tourist_name);

            preg_match('#<li><strong>Apellido: </strong>([\w\d]*)</li>#i', $reservationSection, $matches);
            $tourist_last_name = (count($matches)) ? $matches[1] : "";
           // $output->writeln($tourist_last_name);

            preg_match('#<li><strong>Correo ([&;a-z]+)nico: </strong>([@\.\w\d-_]+)</li>#i', $reservationSection, $matches);
            $tourist_email = (count($matches)) ? $matches[2] : "";
            //$output->writeln($tourist_email);

            preg_match('#<li><strong>Calle: </strong>(.+)</li>#i', $reservationSection, $matches);
            $tourist_address = (count($matches)) ? $matches[1] : "";
            //$output->writeln($tourist_address);

            preg_match('#<li><strong>C([&;a-z]+)digo postal: </strong>(.+)</li>#i', $reservationSection, $matches);
            $tourist_code_postal = (count($matches)) ? $matches[2] : "";
            //$output->writeln($tourist_code_postal);

            preg_match('#<li><strong>Ciudad: </strong>(.+)</li>#i', $reservationSection, $matches);
            $tourist_city = (count($matches)) ? $matches[1] : "";
            //$output->writeln($tourist_city);

            preg_match('#<li><strong>Pa([&;a-z]+)s: </strong>(.+)</li>#i', $reservationSection, $matches);
            $tourist_country = (count($matches)) ? $matches[2] : "";
            //$output->writeln($tourist_country);

            preg_match('#<li><strong>Tel([&;a-z]+)fono: </strong>(.+)</li>#i', $reservationSection, $matches);
            $tourist_phone = (count($matches)) ? $matches[2] : "";
            //$output->writeln($tourist_phone);

            preg_match('#<li><strong>Lenguaje: </strong>(.+)</li>#i', $reservationSection, $matches);
            $tourist_language = (count($matches)) ? $matches[1] : "";
            //$output->writeln($tourist_language);

            preg_match('#<li><strong>Moneda: </strong>(.+)</li>#i', $reservationSection, $matches);
            $tourist_currency = (count($matches)) ? $matches[1] : "";
            //$output->writeln($tourist_currency);

            preg_match('#<li><strong>Adultos: </strong>(.+)</li>#i', $reservationSection, $matches);
            $adults = (count($matches)) ? $matches[1] : 0;
            //$output->writeln($adults);

            preg_match('#<li><strong>Ni([&;a-z]+)os: </strong>(.+)</li>#i', $reservationSection, $matches);
            $kids = (count($matches)) ? $matches[2] : 0;
            //$output->writeln($kids);

            preg_match('#<li><strong>Fecha de Arribo: </strong>(.+)</li>#i', $reservationSection, $matches);
            $arrival_date = (count($matches)) ? $matches[1] : "";
            //$output->writeln($arrival_date);

            preg_match('#<li><strong>Noches: </strong>(.+)</li>#i', $reservationSection, $matches);
            $nights = (count($matches)) ? $matches[1] : 0;
            //$output->writeln($nights);

            preg_match('#<li><strong>Habitaciones: </strong>(.+)</li>#i', $reservationSection, $matches);
            $rooms = (count($matches)) ? $matches[1] : 0;
            //$output->writeln($rooms);

            preg_match('#<li><strong>Comentarios: </strong>(.+)</li>#i', $reservationSection, $matches);
            $comments = (count($matches)) ? $matches[1] : "";
            //$output->writeln($comments);

            preg_match('#<li><strong>Nombre: </strong>(.+)</li>#i', $accommodationSection, $matches);
            $accommodation_name = (count($matches)) ? $matches[1] : "";
            //$output->writeln($accommodation_name);

            preg_match('#<li><strong>Propietarios: </strong>(.+)</li>#i', $accommodationSection, $matches);
            $accommodation_owners = (count($matches)) ? $matches[1] : "";
            //$output->writeln($accommodation_owners);

            preg_match('#<li><strong>Direcc([\s\S]+)n: </strong>(.+)</li>#i', $accommodationSection, $matches);
            $accommodation_address = (count($matches)) ? $matches[2] : "";
            //$output->writeln($accommodation_address);

            preg_match('#<li><strong>Tel([\s\S]+)fono: </strong>(.+)</li>#i', $accommodationSection, $matches);
            $accommodation_phone = (count($matches)) ? $matches[2] : "";
            //$output->writeln($accommodation_phone);

            preg_match('#<li><strong>Celular: </strong>(.+)</li>#i', $accommodationSection, $matches);
            $accommodation_cell = (count($matches)) ? $matches[1] : "";
            //$output->writeln($accommodation_cell);

            $existInDataBase = $em->getRepository("mycpBundle:oldReservation")->findOneBy(array("ref_id" => $messageRefId));

            if ($existInDataBase == null) {
                $item = new oldReservation();
                $item->setRefId($messageRefId)
                    ->setAccommodationAddress($accommodation_address)
                    ->setAccommodationCellphone($accommodation_cell)
                    ->setAccommodationCode($accommodation_code)
                    ->setAccommodationName($accommodation_name)
                    ->setAccommodationOwners($accommodation_owners)
                    ->setAccommodationPhone($accommodation_phone)
                    ->setAdults($adults)
                    ->setArrivalDate($arrival_date)
                    ->setChildren($kids)
                    ->setComments($comments)
                    ->setNights($nights)
                    ->setRooms($rooms)
                    ->setTouristAddress($tourist_address)
                    ->setTouristCity($tourist_city)
                    ->setTouristCountry($tourist_country)
                    ->setTouristCurrency($tourist_currency)
                    ->setTouristEmail($tourist_email)
                    ->setTouristLanguage($tourist_language)
                    ->setTouristLastname($tourist_last_name)
                    ->setTouristName($tourist_name)
                    ->setTouristPhone($tourist_phone)
                    ->setTouristPostalCode($tourist_code_postal)
                    ->setCreationDate($sentDate)
                    ->setReservationCode($reservation_code);

                $em->persist($item);
                $em->flush();
            }
        }
        catch(\Exception $e)
        {
            $output->writeln($messageBody);
            exit;
        }
    }

    private function createOldPayment($messageBody, $sentDate, OutputInterface $output, $messageRefId)
    {
        try{
            $container = $this->getContainer();
            $em = $container->get('doctrine')->getManager();

            //$output->writeln($sentDate);

            preg_match('#<strong>Reservation:</strong>: (.+) </td>#i', $messageBody, $matches);
            $reservation_code = (count($matches)) ? $matches[1] : "";
            //$output->writeln($reservation_code);

            preg_match('#<strong>Cliente:</strong> ([\w\s]+)</td>#i', $messageBody, $matches);
            $client_name = (count($matches)) ? $matches[1] : "";
            //$output->writeln($client_name);

            preg_match('#<strong>Pa([&;a-z]+)s:</strong> ([\w\s]+)</td>#i', $messageBody, $matches);
            $client_country = (count($matches)) ? $matches[2] : "";
            //$output->writeln($client_country);

            preg_match('#<strong>Correo Electr([&;a-z]+)nico:</strong> ([@\.\w\d-_]+)</td>#i', $messageBody, $matches);
            $client_email = (count($matches)) ? $matches[2] : "";
            //$output->writeln($client_email);

            preg_match('#<strong>Casa:</strong> ([\w\s]+)</td>#i', $messageBody, $matches);
            $accommodation_code = (count($matches)) ? $matches[1] : "";
            //$output->writeln($accommodation_code);

            preg_match('#<strong>Habitaciones:</strong> ([\d]+)</td>#i', $messageBody, $matches);
            $rooms = (count($matches)) ? $matches[1] : 0;
            //$output->writeln($rooms);

            preg_match('#<strong>Adultos:</strong> ([\d]+)</td>#i', $messageBody, $matches);
            $adults = (count($matches)) ? $matches[1] : 0;
            //$output->writeln($adults);

            preg_match('#<strong>Ni([&;a-z]+)os:</strong> ([\d]+)</td>#i', $messageBody, $matches);
            $kids = (count($matches)) ? $matches[2] : 0;
            //$output->writeln($kids);

            preg_match('#<strong>Fecha de arrivo:</strong> ([\d/]+)</td>#i', $messageBody, $matches);
            $arrival_date = (count($matches)) ? $matches[1] : "";
            //$output->writeln($arrival_date);

            preg_match('#<strong>Noches:</strong> ([\d]+)</td>#i', $messageBody, $matches);
            $nights = (count($matches)) ? $matches[1] : 0;
            //$output->writeln($nights);

            preg_match('#<strong>A pagar en casa:</strong> ([\d\.]+)</td>#i', $messageBody, $matches);
            $pay_at_service = (count($matches)) ? $matches[1] : 0;
            //$output->writeln($pay_at_service);

            preg_match('#<strong>Saldo Pagado:</strong> ([\w\d\.\s]+)</td>#i', $messageBody, $matches);
            $prepayment_data = (count($matches)) ? $matches[1] : 0;
            //$output->writeln($prepayment_data);

            $prepayment_data = explode(" ", $prepayment_data);
            $payment_currency = $prepayment_data[1];
            $prepayment_amount = floatval($prepayment_data[0]);

            //$output->writeln($payment_currency);
            //$output->writeln($prepayment_amount);

            $existInDataBase = $em->getRepository("mycpBundle:oldPayment")->findOneBy(array("ref_id" => $messageRefId));

            if ($existInDataBase == null) {
                $item = new oldPayment();
                $item->setRefId($messageRefId)
                    ->setRooms($rooms)
                    ->setNights($nights)
                    ->setAccommodationCode($accommodation_code)
                    ->setAdults($adults)
                    ->setArrivalDate($arrival_date)
                    ->setChildren($kids)
                    ->setCurrencyCode($payment_currency)
                    ->setPayAtAccommodation(floatval($pay_at_service))
                    ->setPrepayAmount($prepayment_amount)
                    ->setReservationCode($reservation_code)
                    ->setTouristCountry($client_country)
                    ->setTouristEmail($client_email)
                    ->setTouristFullName($client_name)
                    ->setCreationDate($sentDate);

                $em->persist($item);
                $em->flush();
            }
        }
        catch(\Exception $e)
        {
            $output->writeln($e->getMessage());
            $output->writeln($messageBody);
            exit;
        }
    }

    private function createDate($dateString, $format)
    {
        if($dateString != null && $dateString != "")        {

           return \DateTime::createFromFormat($format, $dateString);
        }
        return null;
    }

}
