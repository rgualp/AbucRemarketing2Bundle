<?php

namespace MyCp\mycpBundle\Command;

use Abuc\RemarketingBundle\Job\PredefinedJobs;
use MyCp\mycpBundle\JobData\CartJobData;
use Abuc\RemarketingBundle\Worker\Worker;
use Abuc\RemarketingBundle\JobData\JobData;
use MyCp\mycpBundle\Entity\user;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FullCartReminderWorkerCommand extends Worker
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
     * 'time' service
     *
     * @var
     */
    private $timer;

    /**
     * 'mycp.service.email_manager' service
     *
     * @var \MyCp\mycpBundle\Helpers\EmailManager
     */
    private $emailManager;
    
    private $em;

    /**
     * {@inheritDoc}
     */
    protected function configureWorker()
    {
        $this
            ->setName('mycp:worker:fullcartreminder')
            ->setDefinition(array())
            ->setDescription('Sends a reminder email after 3h of last cart activity')
            ->setJobName("mycp.job.fullcart.reminder");
    }

    /**
     * {@inheritDoc}
     */
    protected function work(JobData $data,
                            InputInterface $input,
                            OutputInterface $output)
    {
        if(!($data instanceof CartJobData)) {
            throw new \InvalidArgumentException('Wrong datatype received: ' . get_class($data));
        }

        $this->initializeServices();
        $userId = $data->getUserId();

        $output->writeln('Processing Full Cart Reminder for User ID ' . $userId);

        $user = $this->emailManager->getUserById($userId);
        $isCartFullToSendReminder = $this->em->getRepository('mycpBundle:cart')->isFullCartForReminder($userId);

        if (empty($user)) {
            // the user does not exist anymore
            return true;
        }

        $this->emailManager->setLocaleByUser($user);

        if ($isCartFullToSendReminder) {
            $output->writeln('Send Full Cart Reminder Email to User ID ' . $userId);
            $this->sendReminderEmail($user, $output);
        }

        $output->writeln('Successfully finished Full Cart Reminder for User ID ' . $userId);
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

        $emailSubject = $this->translatorService->trans('USER_CART_FULL_REMINDER');
        
        $userTourist = $this->emailManager->getTouristByUser($user);
        $userLocale = strtolower($userTourist->getUserTouristLanguage()->getLangCode());
        $cartItems = $this->em->getCartItemsByUser($userId);
        
        $accommodations = array();
        $cartAccommodations = array();
        $cartPrices = array();
        
        $current_own_id = 0;

        foreach($cartItems as $item)
        {
            if($item->getCartRoom()->getRoomOwnership()->getOwnId() != $current_own_id)
            {
                $current_own_id = $item->getCartRoom()->getRoomOwnership()->getOwnId();
                array_push($accommodations, $item->getCartRoom()->getRoomOwnership());

                $cartAccommodations[$current_own_id] = array();
                $cartPrices[$current_own_id] = array();
            }

            array_push($cartAccommodations[$current_own_id], $item);
            array_push($cartPrices[$current_own_id], $item->calculatePrice($this->em,$this->timer,$this->getContainer()->getParameter('configuration.triple.room.charge'), $this->getContainer()->getParameter('configuration.service.fee')));
        }

        $photos = $this->em->getRepository("mycpBundle:ownership")->get_photos_array($accommodations);

        $emailBody = $this->emailManager->getViewContent(
            'FrontEndBundle:mails:cartFull.html.twig',
            array(
                'owns' => $accommodations,
                'cartItems' => $cartAccommodations,
                'photos' => $photos,
                'prices' => $cartPrices,
                'user_currency' => $userTourist->getUserTouristCurrency(),
                'user_name' => $userName,
                'user_locale' => $userLocale
         ));

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
        $this->timer = $this->getService('Time');
        $this->em = $this->getContainer()->get('doctrine')->getEntityManager();
    }
}
