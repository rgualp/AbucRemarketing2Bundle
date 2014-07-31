<?php

namespace MyCp\mycpBundle\Command;

use Abuc\RemarketingBundle\Job\PredefinedJobs;
use Abuc\RemarketingBundle\JobData\UserIdentificationJobData;
use Abuc\RemarketingBundle\Worker\EmailWorker;
use Abuc\RemarketingBundle\JobData\JobData;
use MyCp\mycpBundle\Entity\user;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class AccountActivationReminderWorkerCommand extends EmailWorker
{
    private $em;
    private $emailService;
    private $templatingService;
    private $translatorService;
    private $securityService;
    private $router;
    private $numRetries;

    /**
     * {@inheritDoc}
     */
    protected function configureWorker()
    {
        $this
            ->setName('mycp:worker:signupreminder')
            ->setDefinition(array())
            ->addArgument('retries', InputArgument::OPTIONAL, 'Number of retries for email sending')
            ->setDescription('Sends a reminder email after 24h if a user has not yet activated his account')
            ->setJobName(PredefinedJobs::JOB_SIGNUP_REMINDER);
    }

    /**
     * {@inheritDoc}
     */
    protected function work(JobData $data,
                            InputInterface $input,
                            OutputInterface $output)
    {
        if(!($data instanceof UserIdentificationJobData)) {
            throw new \InvalidArgumentException('Wrong datatype received: ' . get_class($data));
        }

        $this->initializeServices();
        $userId = $data->getUserId();

        $output->writeln('Processing Account Activation Reminder for User ID ' . $userId);

        $user = $this->getUserById($userId);
        $this->initializeLocale($user);
        $this->initializeNumRetries($input);

        if (!$user->getUserEnabled()) {
            $output->writeln('Send Account Activation Reminder Email to User ID ' . $userId);
            $this->sendReminderEmail($user, $output);
        }

        $output->writeln('Successfully finished Account Activation Reminder for User ID ' . $userId);
        return true;
    }

    /**
     * Sends an account activation reminder email to a user.
     * @param $user
     * @param $output
     */
    private function sendReminderEmail(user $user, OutputInterface $output)
    {
        $userId =  $user->getUserId();
        $userEmail = $user->getUserEmail();

        $emailSubject = $this->translatorService->trans('EMAIL_ACCOUNT_REGISTERED_SUBJECT_REMINDER');
        $activationUrl = $this->getActivationUrl($user);

        $emailBody = $this->templatingService
            ->renderResponse(
                'FrontEndBundle:mails:enableAccountReminder.html.twig',
                array('enableUrl' => $activationUrl)
            )
            ->getContent();

        $output->writeln("Send email to $userEmail, subject '$emailSubject' for User ID $userId");

        for ($numRetries = $this->numRetries; $numRetries > 0; $numRetries--) {
            try {
                $this->emailService->send_templated_email(
                    $emailSubject,
                    'noreply@mycasaparticular.com',
                    $userEmail,
                    $emailBody
                );

                $this->sendOutEmails();
                break;
            } catch (\Swift_TransportException $e) {
                $message = 'Swift_TransportException on Email send-out: ' . PHP_EOL;
                $message .= $e->getMessage() . PHP_EOL;
                $message .= $e->getTraceAsString();

                // retry on transport exceptions
                $this->getLogger()->error($message);
                $output->writeln($message);
            }

            sleep(2);
        }
    }

    /**
     * Initializes the services needed.
     */
    private function initializeServices()
    {
        if (null === $this->em) {
            $this->em = $this->getService('doctrine.orm.entity_manager');
            $this->timeService = $this->getService('time');
            $this->emailService = $this->getService('Email');
            $this->templatingService = $this->getService('templating');
            $this->translatorService = $this->getService('translator');
            $this->securityService = $this->getService('Secure');
            $this->router = $this->getService('router');
        }
    }

    /**
     * Initializes the locale for the translator service.
     *
     * @param user $user
     */
    private function initializeLocale(user $user)
    {
        $userTourist = $this->getTouristByUser($user);
        $userLocale = strtolower($userTourist->getUserTouristLanguage()->getLangCode());
        $this->translatorService->setLocale($userLocale);
    }

    /**
     * Initializes the number of email send-out retries.
     *
     * @param InputInterface $input
     * @throws \InvalidArgumentException
     */
    private function initializeNumRetries(InputInterface $input)
    {
        $numRetries = $input->getArgument('retries');

        if (!$numRetries) {
            $numRetries = 3;
        }

        if (!is_numeric($numRetries)) {
            throw new \InvalidArgumentException('Argument "retries" is not an integer');
        }

        $this->numRetries = (int) $numRetries;
    }

    /**
     * @param $userId
     * @return mixed
     * @throws \LogicException
     */
    private function getUserById($userId)
    {
        $user = $this->em
            ->getRepository('mycpBundle:user')
            ->find($userId);

        if (empty($user)) {
            throw new \LogicException('No user found for ID ' . $userId);
        }

        return $user;
    }

    /**
     * @param $user
     * @return mixed
     * @throws \LogicException
     */
    private function getTouristByUser($user)
    {
        $userTourist = $this->em
            ->getRepository('mycpBundle:userTourist')
            ->findOneBy(array(
                'user_tourist_user' => $user
            ));

        if (empty($userTourist)) {
            throw new \LogicException('No userTourist found for User ID ' . $user->getUserId());
        }

        return $userTourist;
    }

    /**
     * @param $user
     * @return string
     */
    private function getActivationUrl($user)
    {
        $encodedString = $this->securityService
            ->encode_string($user->getUserEmail() . '///' . $user->getUserId());
        $enableUrl = $this->router->generate('frontend_enable_user', array('string' => $encodedString), true);
        return $enableUrl;
    }
}
