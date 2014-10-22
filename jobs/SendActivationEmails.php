<?php

// load_emails.php
require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';
$kernel = new AppKernel('prod', true);
$request = Request::createFromGlobals();
$kernel->boot();
$container = $kernel->getContainer();
$container->enterScope('request');
$container->set('request', $request);
/* end bootstrap */

// Router settings
$router = $container->get('router');
$context = $router->getContext();
$context->setHost('www.mycasaparticular.com');
$context->setScheme('https');
//$context->setBaseUrl('my/path');

$emailService = $container->get('Email');
$securityService = $container->get('Secure');
$translator = $container->get('translator');
$templating = $container->get('templating');

$mailer = $container->get('mailer');
//$spool = $mailer->getTransport()->getSpool();
//$transport = $container->get('swiftmailer.transport.real');

// NUM USERS
$numUsers = 150;

/** @var \Doctrine\Common\Persistence\ObjectManager $em */
$em = $container->get('doctrine')->getManager();

/** @var \Doctrine\ORM\EntityRepository $userRepo */
$userRepo = $em->getRepository('mycpBundle:user');
$userTouristRepo = $em->getRepository('mycpBundle:userTourist');

$query = $userRepo->createQueryBuilder('u')
    ->where('u.user_enabled != 1 or u.user_enabled is null') // restrict users: u.user_id < 2800 AND
    ->orderBy('u.user_id', 'DESC')
    ->setMaxResults($numUsers)
    ->getQuery();

$users = $query->getResult();

echo 'NUMBER OF USERS: ' . count($users) . PHP_EOL;


/** @var \MyCp\mycpBundle\Entity\user $user */
foreach($users as $user) {
    echo '-----------------' . PHP_EOL;
    $userEmail = $user->getUserEmail();
    $userId = $user->getUserId();
    $userName = $user->getUserCompleteName();
    echo 'Id/Email: ' . $userId . '/' . $userEmail . PHP_EOL;

    $userTourist = $userTouristRepo->findOneBy(array('user_tourist_user' => $userId));
    if(empty($userTourist)) continue;

    $language = $userTourist->getUserTouristLanguage()->getLangCode();

    echo 'Language: ' . $language . PHP_EOL;
    $locale = strtolower($language);
    echo $translator->trans('my.translation.key');

    $encode_string = $securityService->encode_string($userEmail . '///' . $userId);
    echo 'Encode: ' . $encode_string . PHP_EOL;

    //mailing
    $enableRoute = 'frontend_enable_user';
    $enableUrl = $router->generate($enableRoute, array('string' => $encode_string, '_locale' => $locale), true);

    echo 'URL: ' . $enableUrl . PHP_EOL;

    $body = $templating->renderResponse('FrontEndBundle:mails:enableAccount.html.twig',
        array('enableUrl' => $enableUrl, 'user_name' =>$userName));

    $translator->setLocale($locale);
    $emailSubject = $translator->trans('EMAIL_ACCOUNT_REGISTERED_SUBJECT');
    echo 'Email Subject: ' . $emailSubject . PHP_EOL;

    try {
        $emailService->send_templated_email($emailSubject,
            'noreply@mycasaparticular.com', $userEmail, $body->getContent());
    } catch(Exception $ex) {
        echo 'EMAIL EXCEPTION: ' . PHP_EOL;
        echo $ex->getMessage() . PHP_EOL;
    }


// now manually flush the queue
    //$spool->flushQueue($transport);
}

