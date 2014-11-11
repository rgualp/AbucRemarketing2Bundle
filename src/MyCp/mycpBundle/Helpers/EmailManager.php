<?php

namespace MyCp\mycpBundle\Helpers;

use Doctrine\ORM\EntityManager;
use MyCp\FrontEndBundle\Helpers\Email;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\userTourist;

/**
 * Class EmailManager
 *
 * Convienience class to use re-use email functionality as a service.
 *
 * @package MyCp\mycpBundle\Helpers
 */
class EmailManager
{
    /**
     * 'doctrine.orm.entity_manager' service
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * 'Email' service
     *
     * @var Email
     */
    private $emailService;

    /**
     * 'templating' service
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templatingService;

    /**
     * 'translator' service
     *
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translatorService;

    /**
     * 'router' service
     *
     * @var
     */
    private $router;

    /**
     * Number of email sending retries
     *
     * @var int
     */
    private $numRetries;

    /**
     * The email spool
     * @var \Swift_Spool
     */
    private $spool;

    /**
     * 'swiftmailer.transport.real' service
     *
     * @var \Swift_Transport
     */
    private $mailerTransport;

    /**
     * 'mailer' service
     *
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * 'logger' service
     *
     * @var
     */
    private $logger;

    /**
     * The sender address of emails being sent.
     *
     * @var string
     */
    private $emailSenderAddress;

    /**
     * The sender name of emails being sent.
     *
     * @var string
     */
    private $emailSenderName;

    /**
     * @param EntityManager $em
     * @param Email $emailService
     * @param $templatingService
     * @param $translatorService
     * @param $router
     * @param $mailer
     * @param $mailerTransport
     * @param $logger
     * @param string $emailSenderAddress
     * @param string $emailSenderName
     * @param int $numEmailSendOutRetries
     */
    public function __construct(EntityManager $em,
                                Email $emailService,
                                $templatingService,
                                $translatorService,
                                $router,
                                $mailer,
                                $mailerTransport,
                                $logger,
                                $emailSenderAddress = 'noreply@mycasaparticular.com',
                                $emailSenderName = 'MyCasaParticular.com',
                                $numEmailSendOutRetries = 3)
    {
        $this->em = $em;
        $this->emailService = $emailService;
        $this->templatingService = $templatingService;
        $this->translatorService = $translatorService;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->mailerTransport = $mailerTransport;
        $this->spool = $this->mailer->getTransport()->getSpool();
        $this->logger = $logger;
        $this->numRetries = $numEmailSendOutRetries;
        $this->emailSenderAddress = $emailSenderAddress;
        $this->emailSenderName = $emailSenderName;
    }

    /**
     * Returns the content of a view.
     *
     * @param string $view The view
     * @param array $parameters The parameters to render the view.
     * @return string
     */
    public function getViewContent($view, array $parameters = array())
    {
        return $this->templatingService
            ->renderResponse($view, $parameters)
            ->getContent();
    }

    /**
     * Sends an email by embedding the body into the standard template.
     *
     * @param string $emailAddress
     * @param string $emailSubject
     * @param string $emailBody
     * @param string $emailFrom
     */
    public function sendTemplatedEmail($emailAddress, $emailSubject, $emailBody, $emailFrom = null)
    {
        if (null === $emailFrom) {
            $emailFrom = $this->emailSenderAddress;
        }

        $callback = array($this->emailService, 'sendTemplatedEmail');
        $parameters = array($emailSubject, $emailFrom, $emailAddress, $emailBody);
        $this->sendEmailWithRetries($callback, $parameters);
    }

    /**
     * Sends an email with the specified body.
     *
     * @param string $emailAddress
     * @param string $emailSubject
     * @param string $emailBody
     * @param string $emailFrom
     * @param null|resource $attachment
     */
    public function sendEmail($emailAddress, $emailSubject, $emailBody, $emailFrom = null, $attachment = null)
    {
        if (null === $emailFrom) {
            $emailFrom = $this->emailSenderAddress;
        }

        $callback = array($this->emailService, 'sendEmail');
        $parameters = array($emailSubject, $emailFrom, $this->emailSenderName,
            $emailAddress, $emailBody, $attachment);
        $this->sendEmailWithRetries($callback, $parameters);
    }

    /**
     * Triggers the email spool so that emails are being sent out immediately.
     */
    public function forceSendOutEmails()
    {
        // for memory spools we have to manually flush the queue
        $this->spool->flushQueue($this->mailerTransport);
    }

    /**
     * Convienience method to retrieve the user belonging to a userId.
     *
     * @param int|string $userId
     * @return null|user
     */
    public function getUserById($userId)
    {
        $user = $this->em
            ->getRepository('mycpBundle:user')
            ->find($userId);

        if (empty($user)) {
            return null;
        }

        return $user;
    }

    /**
     * Convienience method to retrieve the userTourist linked to a user.
     *
     * @param $user
     * @return userTourist
     * @throws \LogicException
     */
    public function getTouristByUser(user $user)
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
     * Initializes the locale for the translator service
     * with the locale of the specific user.
     *
     * @param user $user
     */
    public function setLocaleByUser(user $user)
    {
        $userLocale = $this->getUserLocale($user);
        $this->translatorService->setLocale($userLocale);
    }

    /**
     * Returns the locale of a user.
     *
     * @param user $user
     * @return string
     */
    public function getUserLocale(user $user)
    {
        $userTourist = $this->getTouristByUser($user);
        $userLocale = strtolower($userTourist->getUserTouristLanguage()->getLangCode());
        return $userLocale;
    }

    /**
     * Sends an email and retries it in case of a transport error.
     *
     * @param $callback
     * @param array $parameters
     * @throws \Exception
     * @throws null
     * @throws \Swift_TransportException
     */
    private function sendEmailWithRetries($callback, array $parameters = array())
    {
        $exception = null;

        for ($numRetries = $this->numRetries; $numRetries > 0; $numRetries--) {
            try {
                // to avoid SwiftMailer connection problems start and stop mailerTransport before and after sending
                // @see http://www.prowebdev.us/2013/06/swiftmailersymfony2-expected-response.html
                $this->mailerTransport->start();

                call_user_func_array($callback, $parameters);
                $this->forceSendOutEmails();

                $this->mailerTransport->stop();
                return;
            } catch (\Swift_TransportException $e) {
                $message = 'Swift_TransportException on Email send-out: ' . PHP_EOL;
                $message .= $e->getMessage() . PHP_EOL;
                $message .= $e->getTraceAsString();

                // retry on transport exceptions
                $this->logger->error($message);
                $exception = $e;

                if ($this->mailerTransport->isStarted()) {
                    $this->mailerTransport->stop();
                }
            }

            sleep(5);
        }

        throw $exception;
    }
}
