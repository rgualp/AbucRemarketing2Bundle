<?php

namespace MyCp\mycpBundle\Command;

use Abuc\RemarketingBundle\Job\PredefinedJobs;
use Abuc\RemarketingBundle\JobData\UserIdentificationJobData;
use Abuc\RemarketingBundle\Worker\Worker;
use Abuc\RemarketingBundle\JobData\JobData;
use MyCp\mycpBundle\Entity\user;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AccountActivationReminderWorkerCommand extends Worker
{
    /**
     * 'translator' service
     * @var
     */
    private $translatorService;

    /**
     * 'Secure' service
     *
     * @var
     */
    private $securityService;

    /**
     * 'router' service
     *
     * @var
     */
    private $router;

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
            ->setName('mycp:worker:signupreminder')
            ->setDefinition(array())
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

        $user = $this->emailManager->getUserById($userId);// $this->getUserById($userId);
        $this->emailManager->setLocaleByUser($user);

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
        $userName = $user->getUserCompleteName();

        $emailSubject = $this->translatorService->trans('EMAIL_ACCOUNT_REGISTERED_SUBJECT_REMINDER');
        $userLocale = $this->emailManager->getUserLocale($user);
        $activationUrl = $this->getActivationUrl($user, $userLocale);

        $emailBody = $this->emailManager->getViewContent(
            'FrontEndBundle:mails:enableAccountReminder.html.twig',
            array('enableUrl' => $activationUrl, 
                  'user_name' => $userName,
                  'user_locale' => $userLocale  )
        );

        $output->writeln("Send email to $userEmail, subject '$emailSubject' for User ID $userId");
        $this->emailManager->sendTemplatedEmail($userEmail, $emailSubject, $emailBody);
    }

    /**
     * Initializes the services needed.
     */
    private function initializeServices()
    {
        $this->emailManager = $this->getService('mycp.service.email_manager');
        $this->translatorService = $this->getService('translator');
        $this->securityService = $this->getService('Secure');
        $this->router = $this->getService('router');
    }

    /**
     * @param $user
     * @return string
     */
    private function getActivationUrl($user, $userLocale)
    {
        $encodedString = $this->securityService->getEncodedUserString($user);
        $enableUrl = $this->router->generate('frontend_enable_user', array(
            'string' => $encodedString,
            'locale' => $userLocale,
            '_locale' => $userLocale
            ), true);
        return $enableUrl;
    }
}
