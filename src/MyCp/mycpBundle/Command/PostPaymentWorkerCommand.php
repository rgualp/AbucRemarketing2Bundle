<?php

namespace MyCp\mycpBundle\Command;

use Abuc\RemarketingBundle\JobData\JobData;
use Abuc\RemarketingBundle\Worker\Worker;
use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\JobData\PaymentJobData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class PostPaymentWorkerCommand
 *
 * Worker for sending out last reservation reminder emails to customers.
 *
 * @package MyCp\mycpBundle\Command
 */
class PostPaymentWorkerCommand extends Worker
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
     * 'translator' service
     * @var
     */
    private $bookingService;

    /**
     * {@inheritDoc}
     */
    protected function configureWorker()
    {
        $this
            ->setName('mycp:worker:postpaymentreminder')
            ->setDefinition(array())
            ->setDescription('Triggers after 1 hour of a user made a successful payment')
            ->setJobName('mycp.job.postpayment.reminder');
    }

    /**
     * {@inheritDoc}
     */
    protected function work(JobData $data,
                            InputInterface $input,
                            OutputInterface $output)
    {
        if(!($data instanceof PaymentJobData)) {
            throw new \InvalidArgumentException('Wrong datatype received: ' . get_class($data));
        }

        $this->output = $output;
        $this->initializeServices();
        $paymentId = $data->getPaymentId();

        $output->writeln('Processing Post Payment Process for Payment ID ' . $paymentId);

        /** @var generalReservation $reservationId */
        $payment = $this->em
                ->getRepository('mycpBundle:payment')
                ->find($paymentId);

        if (!$payment->getProcessed()) {
            //uncomment following line
            //$this->bookingService->postProcessBookingPayment($payment);

            $payment->setProcessed(true);
            $this->em->persist($payment);

        }

        $output->writeln('Successfully finished Post Payment process for Payment ID ' . $paymentId);

        return true;
    }

    /**
     * Initializes the services needed.
     */
    private function initializeServices()
    {
        $this->em = $this->getService('doctrine.orm.entity_manager');
        $this->bookingService = $this->getService('front_end.services.booking');
    }
}
