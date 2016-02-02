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
    private $em;

    private $container;

    private $service_email;

    protected function configure()
    {
        $this
            ->setName('mycp_task:create_accounts')
            ->setDefinition(array())
            ->addOption('see-ownership-only', null, InputOption::VALUE_NONE, 'See total ownership without usercasa')
            ->addOption('see-usercasa-disabled', null, InputOption::VALUE_NONE, 'See total userCasa disabled')
            ->addOption('create-usercasa', null, InputOption::VALUE_NONE, 'Create userCasa to users ownership')
            ->addOption('create-usercasa-send-email', null, InputOption::VALUE_NONE, 'Create userCasa to users ownership and send email')
            ->addOption('send-email-usercasa-disabled', null, InputOption::VALUE_NONE, 'Send email to userCasa disabled')
            ->addOption('send-email-usercasa-disabled-apologies', null, InputOption::VALUE_NONE, 'Send email to userCasa disabled apologies')
            ->setDescription('Create accounts');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig();
        $see_ownership_only= $input->getOption('see-ownership-only');
        $see_usercasa_disabled= $input->getOption('see-usercasa-disabled');
        $create_usercasa= $input->getOption('create-usercasa');
        $create_usercasa_send_email= $input->getOption('create-usercasa-send-email');
        $send_email_usercasa_disabled= $input->getOption('send-email-usercasa-disabled');
        $send_email_usercasa_disabled_apologies= $input->getOption('send-email-usercasa-disabled-apologies');
        $cont_error= 0;

        if($see_ownership_only){
            $results= $this->getOwnershipsNoUserCasa();
            $total= count($results);
            $output->writeln('<info>Existen  '.$total.' ownerships que no son userCasa que tienen email y estan activos.</info>');
            return;
        }

        if($see_usercasa_disabled){
            $results= $this->getUserCasaDisabled();
            $total= count($results);
            $output->writeln('<info>Existen  '.$total.' userCasa que no estan habilitados.</info>');

            /***Test***/
            //Comprobar que los estados son distintos de 1 (enabled, es decir, que sean 0 o NULL)
            $out= '';
            foreach ($results as $user_casa) {
                $out.= $user_casa->getUserCasaUser()->getUserEnabled().'-';
            }
            $output->writeln('<info>'.$out.'</info>');
            /***Test End***/
            return;
        }

        if($create_usercasa || $create_usercasa_send_email){
            $output->writeln('<info>Extracting  ownership</info>');
            $results= $this->getOwnershipsNoUserCasa();
            $total= count($results);
            $output->writeln('<info>Extract '.$total.' ownerships</info>');
            $output->writeln('<info>Creating users...</info>');
            $index=1;
            foreach ($results as $ownership) {
                /***See***/
                $email = trim($ownership->getOwnEmail1());
                if (empty($email))
                    $email = trim($ownership->getOwnEmail2());
                $output->writeln('<info>'.$index++.' Creating userCasa: '.$email.'</info>');
                /***See END***/

                /***Creando userCasa***/
                try{
                    $user_casa= $this->createUser($ownership);
                    $this->em->flush();
                    /***Email***/
                    if($create_usercasa_send_email){
                        $output->writeln('<info>Preparing to send "'.$email.'"...</info>');
                        $this->service_email->sendCreateUserCasaMailCommand($user_casa, $ownership);
                        $output->writeln('<info>Queued</info>');
                    }
                    /***Email End***/

                } catch (\Exception $e) {
                    $cont_error++;
                    $error= $e->getMessage();
                    $output->writeln('<error>Error with user: '.$email.'->'.$error.'</error>');
                }
                /***Creando userCasa END***/
            }

        }

        if($send_email_usercasa_disabled){
            $output->writeln('<info>Extracting  usersCasa...</info>');
            $results= $this->getUserCasaDisabled();
            $total= count($results);
            $output->writeln('<info>Extract '.$total.' usersCasa</info>');

            $index=1;
            foreach ($results as $user_casa) {
                $email= $user_casa->getUserCasaUser()->getUserEmail();
                $output->writeln('<info>'.$index++.': Preparing "'.$email.'"...</info>');
                $ownership= $user_casa->getUserCasaOwnership();
                try{
                    $this->service_email->sendCreateUserCasaMailCommand($user_casa, $ownership);
                    $output->writeln('<info>Queued</info>');
                }catch (\Exception $e) {
                    $cont_error++;
                    $error= $e->getMessage();
                    $output->writeln('<error>Error with user: '.$email.'->'.$error.'</error>');
                }
            }
        }

        if($send_email_usercasa_disabled_apologies){
            $output->writeln('<info>Extracting  usersCasa...</info>');
            $results= $this->getUserCasaDisabled();
            $total= count($results);
            $output->writeln('<info>Extract '.$total.' usersCasa</info>');

            $index=1;
            foreach ($results as $user_casa) {
                $email= $user_casa->getUserCasaUser()->getUserEmail();
                $output->writeln('<info>'.$index++.': Preparing "'.$email.'"...</info>');
                $ownership= $user_casa->getUserCasaOwnership();
                try{
                    $this->service_email->sendCreateUserCasaMailApologiesCommand($user_casa, $ownership);
                    $output->writeln('<info>Queued</info>');
                }catch (\Exception $e) {
                    $cont_error++;
                    $error= $e->getMessage();
                    $output->writeln('<error>Error with user: '.$email.'->'.$error.'</error>');
                }
            }
        }

        if($cont_error>0){
            $output->writeln('<error>'.$cont_error.' usuarios con problemas</error>');
        }
        $output->writeln('<info>Finish (Waiting send emails)...</info>');
    }

    protected function createUser($ownership) {
        $container = $this->getContainer();
        $factory = $container->get('security.encoder_factory');

        $user = new user();
        $country = $this->em->getRepository('mycpBundle:country')->findBy(array('co_name' => 'Cuba'));
        $subrole = $this->em->getRepository('mycpBundle:role')->findOneBy(array('role_name' => 'ROLE_CLIENT_CASA'));

        $address = $ownership->getOwnAddressStreet() . " #" . $ownership->getOwnAddressNumber() . ", " . $ownership->getOwnAddressMunicipality()->getMunName() . ", " . $ownership->getOwnAddressProvince()->getProvName();
        $phone = '(+53) ' . $ownership->getOwnAddressProvince()->getProvPhoneCode() . ' ' . $ownership->getOwnPhoneNumber();

        $email = trim($ownership->getOwnEmail1());
        if (empty($email))
            $email = trim($ownership->getOwnEmail2());

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

        $this->em->persist($user);
        $this->em->persist($user_casa);

        return $user_casa;
    }

    protected function getOwnershipsNoUserCasa(){
        $sql= 'select o from mycpBundle:ownership o ';
        $sql.= 'LEFT OUTER JOIN ';
        $sql.= 'mycpBundle:userCasa uc ';
        $sql.= 'WITH o.own_id = uc.user_casa_ownership ';
        $sql.= 'WHERE uc.user_casa_ownership IS NULL ';//Condicionar que no existe en la tabla "userCasa"
        $sql.= "AND (o.own_email_1 !='' OR o.own_email_2 !='') ";//Asegurar que tiene email
        $sql.= "AND o.own_status = 1";//Status 1=Activo
        /***test***/
        //AR001,AR002,AR003,AR004
//        $sql.= "AND o.own_mcp_code in ('AR001','AR002','AR003','AR004') ";
        /***test END***/

        $q = $this->em->createQuery(trim($sql));
        $results= $q->execute();
        return $results;

    }

    protected function getUserCasaDisabled(){
        $sql= 'select uc from mycpBundle:userCasa uc ';
        $sql.= 'INNER JOIN ';
        $sql.= 'mycpBundle:user u ';
        $sql.= 'WITH u = uc.user_casa_user ';
        $sql.= 'INNER JOIN ';
        $sql.= 'mycpBundle:ownership o ';
        $sql.= 'WITH o = uc.user_casa_ownership ';
        $sql.= 'WHERE u.user_enabled <> 1 ';
        /***test***/
        //AR001,AR002,AR003,AR004
//        $sql.= "AND o.own_mcp_code in ('AR001','AR002','AR003','AR004') ";
        /***test END***/

        $q = $this->em->createQuery(trim($sql));
        $results= $q->execute();
        return $results;
    }

    protected function loadConfig(){
        $this->container = $this->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
        $this->service_email = $this->container->get('Email');
    }
}
