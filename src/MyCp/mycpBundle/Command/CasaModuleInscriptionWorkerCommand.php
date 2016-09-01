<?php

namespace MyCp\mycpBundle\Command;

use Abuc\RemarketingBundle\JobData\JobData;
use Abuc\RemarketingBundle\Worker\Worker;
use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\JobData\PaymentJobData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class CasaModuleInscriptionWorkerCommand
 *
 * Worker for sending out last reservation reminder emails to customers.
 *
 * @package MyCp\mycpBundle\Command
 */
class CasaModuleInscriptionWorkerCommand extends Worker
{
    /**
     * @var  OutputInterface
     */
    private $output;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * 'mycp.service.email_manager' service
     *
     * @var \MyCp\mycpBundle\Helpers\EmailManager
     */
    private $emailManager;

    /**
     * {@inheritDoc}
     */
    protected function configureWorker()
    {
        $this
            ->setName('mycp:worker:casamoduleinscriptionreminder')
            ->setDefinition(array())
            ->setDescription('Triggers after 24 hours a user insert an accommodation in Casa Module and is still not active')
            ->setJobName('mycp.job.casamodule.inscription.reminder');
    }

    /**
     * {@inheritDoc}
     */
    protected function work(JobData $data,
                            InputInterface $input,
                            OutputInterface $output)
    {
        if(!($data instanceof AccommodationJobData)) {
            throw new \InvalidArgumentException('Wrong datatype received: ' . get_class($data));
        }

        $this->output = $output;
        $this->initializeServices();
        $accommodationId = $data->getAccommodationId();

        $output->writeln('Processing reminder inscription email for accommodation ID ' . $accommodationId);

        /** @var generalReservation $reservationId */
        $accommodation = $this->em
                ->getRepository('mycpBundle:ownership')
                ->find($accommodationId);

        if (!$accommodation->isActive() and $accommodation->getInsertedInCasaModule()) {
            //Enviar correo a los propietarios
            $this->sendReminderEmail($accommodation, $output);

        }

        $output->writeln('Successfully sent reminder inscription email to owners of ' . $accommodationId);

        return true;
    }

    /**
     * Sends an reminder email to accommodation's owner.
     * @param $accommodation
     * @param $output
     */
    private function sendReminderEmail(ownership $accommodation, OutputInterface $output)
    {
        $accommodationId =  $accommodation->getOwnId();
        $accommodationEmail = ($accommodation->getOwnEmail1()) ? $accommodation->getOwnEmail1() : $accommodation->getOwnEmail2();
        $userName = ($accommodation->getOwnHomeowner1()) ? $accommodation->getOwnHomeowner1() : $accommodation->getOwnHomeowner2();

        $emailSubject ="Únete a MyCasaParticular.com";

        $emailBody = $this->emailManager->getViewContent('FrontEndBundle:mails:casamodule_inscription_reminder.html.twig',
            array('user_name' => $userName,
                  'user_locale' => "es"));

        $output->writeln("Send email to $accommodationEmail, subject '$emailSubject' for accommodation ID $accommodationId");
        $this->emailManager->sendEmail($accommodationEmail, $emailSubject, $emailBody);
    }

    /**
     * Initializes the services needed.
     */
    private function initializeServices()
    {
        $this->em = $this->getService('doctrine.orm.entity_manager');
        $this->emailManager = $this->getService('mycp.service.email_manager');
    }
}
