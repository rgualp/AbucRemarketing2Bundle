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


class CreateAccountsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mycp_task:create_accounts')
            ->setDefinition(array())
            ->setDescription('Create accounts');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $cont_error= 0;
        $data_users= array();

        $output->writeln('<info>Extracting  ownership</info>');

        $sql= 'select o from mycpBundle:ownership o ';
        $sql.= 'LEFT OUTER JOIN ';
        $sql.= 'mycpBundle:userCasa uc ';
        $sql.= 'WITH o.own_id = uc.user_casa_ownership ';
        $sql.= 'WHERE uc.user_casa_ownership IS NULL ';//Condicionar que no existe en la tabla "userCasa"
        $sql.= "AND (o.own_email_1 !='' OR o.own_email_2 !='') ";//Asegurar que tiene email
        $sql.= "AND o.own_status = 1";//Status 1=Activo

        $q = $em->createQuery($sql);
        $results= $q->execute();
        $total= count($results);

        $output->writeln('<info>Extract '.$total.' ownerships</info>');
        $output->writeln('<info>Creating users...</info>');
        foreach ($results as $ownership) {
            /***See***/
            $email = $ownership->getOwnEmail1();
            if (empty($email))
                $email = $ownership->getOwnEmail2();
            $output->writeln('<info>Creating: '.$email.'</info>');
            /***See END***/

            /***Creando userCasa***/
            try{
                $user_casa= $this->createUser($ownership, $em);
                $data_users[]= array($user_casa, $ownership);
                $em->flush();
            } catch (\Exception $e) {
                $cont_error++;
                $error= $e->getMessage();
                $output->writeln('<error>Error with user: '.$email.'->'.$error.'</error>');
            }
            /***Creando userCasa END***/
        }

        /*** Send emails ***/
        $this->sendEmails($data_users);
        /*** Send emails ***/

        if($cont_error>0){
            $output->writeln('<error>'.$cont_error.' usuarios dejados de crear</error>');
        }

        $output->writeln('<info>Finish</info>');

    }

    protected function createUser($ownership, $em) {
        $container = $this->getContainer();
        $factory = $container->get('security.encoder_factory');

        $user = new user();
        $country = $em->getRepository('mycpBundle:country')->findBy(array('co_name' => 'Cuba'));
        $subrole = $em->getRepository('mycpBundle:role')->findOneBy(array('role_name' => 'ROLE_CLIENT_CASA'));

        $address = $ownership->getOwnAddressStreet() . " #" . $ownership->getOwnAddressNumber() . ", " . $ownership->getOwnAddressMunicipality()->getMunName() . ", " . $ownership->getOwnAddressProvince()->getProvName();
        $phone = '(+53) ' . $ownership->getOwnAddressProvince()->getProvPhoneCode() . ' ' . $ownership->getOwnPhoneNumber();

        $email = $ownership->getOwnEmail1();
        if (empty($email))
            $email = $ownership->getOwnEmail2();

        $user->setUserAddress($address);
        $user->setUserCity($ownership->getOwnAddressMunicipality()->getMunName());
        $user->setUserCountry($country[0]);
        $user->setUserEmail($email);
        $user->setUserPhone($phone);
        $user->setUserName($ownership->getOwnMcpCode());

        $user->setUserRole('ROLE_CLIENT_CASA');
        $user->setUserEnabled(false);
        $user->setUserCreatedByMigration(false);
        $user->setUserSubrole($subrole);
        $user_name = explode(' ', $ownership->getOwnHomeOwner1());
        $user->setUserUserName($user_name[0]);
        $lastName = (count($user_name) > 1) ? $user_name[1] : "";
        $user->setUserLastName($lastName);
        $user->setUserPassword(" ");

        $user_casa = new userCasa();
        $user_casa->setUserCasaOwnership($ownership);
        $user_casa->setUserCasaUser($user);
        $encoder = $factory->getEncoder($user);
        $secret_token = $encoder->encodePassword("casa_" . $ownership->getOwnMcpCode(), $user->getSalt());
        $secret_token = base64_encode($secret_token);
        $secret_token = str_replace('/', '1', $secret_token);
        $secret_token = str_replace(' ', '2', $secret_token);
        $secret_token = str_replace('+', '3', $secret_token);
        $secret_token = str_replace('=', '4', $secret_token);
        $secret_token = str_replace('?', '5', $secret_token);
        $user_casa->setUserCasaSecretToken($secret_token);

        $em->persist($user);
        $em->persist($user_casa);

        return $user_casa;
    }

    protected function sendEmails($data_users){
        $container= $this->getContainer();
        $templating = $container->get('templating');
        $message = \Swift_Message::newInstance();
        $replacements=array();

        foreach($data_users as list($user_casa, $ownership)){
            $user = $user_casa->getUserCasaUser();
            $user_fullname= trim($user->getUserUserName() . ' ' . $user->getUserLastName());
            $email_to= $user->getUserEmail();

            $replacements[$email_to]=array(
                '{user_name}'=>$user->getUserName(),
                '{user_full_name}'=>$user_fullname,
                '{own_name}'=>$ownership->getOwnName(),
                '{own_mycp_code}'=>$ownership->getOwnMcpCode(),
                '{secret_token}'=>$user_casa->getUserCasaSecretToken()
            );


            $message->addTo($email_to);
        }
        $message->setFrom('casa@mycasaparticular.com', 'MyCasaParticular.com');
        $message->setSubject("Creación de cuenta de usuario");
        $template= 'FrontEndBundle:mails:createUserCasaMailBodyCommand.html.twig';
        $message->setBody($templating->renderResponse($template), 'text/html');
        $decorator= new \Swift_Plugins_DecoratorPlugin($replacements);
        $mailer= $container->get('mailer');
        $mailer->registerPlugin($decorator);
        $mailer->send($message);
    }
}
