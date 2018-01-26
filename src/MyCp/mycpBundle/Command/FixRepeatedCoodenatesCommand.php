<?php
/**
 * Created by PhpStorm.
 * User: vhagar
 * Date: 25/01/2018
 * Time: 09:17
 */

namespace MyCp\mycpBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixRepeatedCoodenatesCommand extends ContainerAwareCommand
{
    protected function configure() {
        $this
            ->setName('mycp:fixcoordenates')
            ->setDefinition(array())
            ->setDescription('Find and fix repeated coodenates');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $logger = $container->get('logger');
        $output->writeln(date(DATE_W3C) . ': Starting  command...');





        try {
            $ownerships = $em->getRepository('mycpBundle:ownership')->getRepeatedCoodenates();

        } catch (\Exception $e) {
            $message = "Could not send Email" . PHP_EOL . $e->getMessage();
            $logger->warning($message);
            $output->writeln($message);
        }

        /* Actualizar el campo de sended */
        if(count($ownerships) > 0) {
            $output->writeln('Updating data in database...');
            foreach ($ownerships as $entity) {
                $own = $em->getRepository("mycpBundle:ownership")->find($entity["ownId"]);
                $municipio=$own->getOwnAddressMunicipality()->getMunName();
                $provincia=$own->getOwnAddressProvince()->getProvName();
                $calle=$own->getOwnAddressStreet();
                $address=$calle.','.$provincia.','.$municipio.','.'Cuba';
                $result=$this->lookup($address);

                if($result!=null) {

                    $own->setOwnGeolocateX($result['x']);
                    $own->setOwnGeolocateY($result['y']);
                    $em->persist($own);
                    $em->flush();
                    $output->writeln('Updating data in database...'.$entity["own_mcp_code"]);
                }
            }

        }

        $output->writeln('Operation completed!!!');
        return 0;
    }


    function lookup($string){

        $string = str_replace (" ", "+", urlencode($string));
        $details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $details_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($ch), true);

        // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
        if ($response['status'] != 'OK') {
            return null;
        }


        $geometry = $response['results'][0]['geometry'];

        $longitude = $geometry['location']['lat'];
        $latitude = $geometry['location']['lng'];

        $array = array(
            'y' => $geometry['location']['lng'],
            'x' => $geometry['location']['lat'],
            'location_type' => $geometry['location_type'],
        );

        return $array;

    }
}