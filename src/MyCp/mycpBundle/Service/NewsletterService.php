<?php

namespace MyCp\mycpBundle\Service;

use MyCp\mycpBundle\Entity\newsletter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NewsletterService extends Controller
{
	private $smsService;
	private $mailService;
    private $entityManager;
    private $templating;

	private $testingMode;
	private $testingEmail;
	private $testingSms;


	public function __construct($smsService, $mailService, $entityManager, $templating, $testingMode, $testingEmail, $testingSms)
	{
		$this->smsService = $smsService;
		$this->mailService = $mailService;
        $this->entityManager = $entityManager;
        $this->templating = $templating;

		$this->testingMode = $testingMode;
		$this->testingEmail = $testingEmail;
        $this->testingSms = $testingSms;
	}

    public function sendNewsletterByCode($newsletterCode)
    {
        $newsletter = $this->entityManager->getRepository("mycpBundle:newsletter")->findOneBy(array("code" => $newsletterCode));

        if($newsletter != null)
           return $this->sendNewsletter($newsletter);

        return false;
    }

    public function sendNewsletterById($newsletterId)
    {
        $newsletter = $this->entityManager->getRepository("mycpBundle:newsletter")->find($newsletterId);

        if($newsletter != null)
            return $this->sendNewsletter($newsletter);

        return false;
    }

    public function sendNewsletter($newsletter)
    {
       // try {
            if((!$this->testingMode && !$newsletter->getSent()) || $this->testingMode) {
                //Determinar el tipo del newsletter para usar un servicio u otro
                if ($newsletter->getType() == newsletter::NEWSLETTER_TYPE_EMAIL)
                    $this->sendNewsletterEmail($newsletter);
                elseif ($newsletter->getType() == newsletter::NEWSLETTER_TYPE_SMS)
                    $this->sendNewsletterSms($newsletter);

                if(!$this->testingMode) {
                    //Actualzar en la base de datos que ese newsletter se envio
                    $newsletter->setSent(true)
                        ->setSentDate(new \DateTime());

                    $this->entityManager->persist($newsletter);
                    $this->entityManager->flush();
                }

                return true;
            }
        /*}
        catch(\Exception $e)
        {
            return false;
        }*/
    }

    private function sendNewsletterEmail($newsletter){
        //Si es correo, determinar el template
        $baseTemplateEmail = "";
        $sender = "";

        switch($newsletter->getUsersRole())
        {
            case "ROLE_CLIENT_CASA": {
                $baseTemplateEmail = "FrontEndBundle:mails:newsletter_accommodations.html.twig";
                $sender = "casa@mycasaparticular.com";
                break;
            }
            case "ROLE_CLIENT_TOURIST": {
                $baseTemplateEmail = "FrontEndBundle:mails:newsletter_tourists.html.twig";
                $sender = "reservation@mycasaparticular.com";
                break;
            }
            default: {
                $baseTemplateEmail = "FrontEndBundle:mails:newsletter_tourists.html.twig";
                $sender = "info@mycasaparticular.com";
                break;
            }
        }

        $langs = $this->entityManager->getRepository("mycpBundle:lang")->findAll();

        foreach($langs as $lang)
        {
            $content = $this->entityManager->getRepository("mycpBundle:newsletterContent")->findOneBy(array(
                "newsletter" => $newsletter->getId(),
                "language" => $lang->getLangId()
            ));

            if($content != null) {
                if ($this->testingMode) {
                    $this->sendEmailUsingService($baseTemplateEmail, $sender, $content, "Testing User", $this->testingEmail, $newsletter->getCode(), $lang->getLangCode());
                } else {

                    $contacts = $this->entityManager->getRepository("mycpBundle:newsletterEmail")->findOneBy(array(
                        "newsletter" => $newsletter->getId(),
                        "language" => $lang->getLangId()
                    ));

                    foreach ($contacts as $contact) {
                        $this->sendEmailUsingService($baseTemplateEmail, $sender, $content, $contact->getName(), $contact->getEmail(), $newsletter->getCode(), $lang->getLangCode());
                    }
                }
            }
        }
    }

    private function sendNewsletterSms($newsletter){
        $langs = $this->entityManager->getRepository("mycpBundle:lang")->findAll();

        foreach($langs as $lang)
        {
            $content = $this->entityManager->getRepository("mycpBundle:newsletterContent")->findOneBy(array(
                "newsletter" => $newsletter->getId(),
                "language" => $lang->getLangId()
            ));

            if($content != null) {
                if ($this->testingMode) {
                    $this->smsService->sendSMSNotification($this->testingSms, $content->getEmailBody(), $newsletter->getCode());
                } else {

                    $contacts = $this->entityManager->getRepository("mycpBundle:newsletterEmail")->findBy(array(
                        "newsletter" => $newsletter->getId(),
                        "language" => $lang->getLangId()
                    ));

                    foreach ($contacts as $contact) {
                        $this->smsService->sendSMSNotification($contact->getMobile(), $content->getEmailBody(), $newsletter->getCode());
                    }
                }
            }
        }
    }

    private function sendEmailUsingService($baseTemplateEmail, $sender, $content, $contactName, $contactEmail, $newsletterCode, $langCode)
    {
        $emailBody = $this->templating->renderResponse($baseTemplateEmail, array(
            "user_name" => $contactName,
            "user_locale" => strtolower($langCode),
            "content" => $content->getEmailBody()
        ));

        //$this->mailService->sendTemplatedEmail($contactEmail, $content->getSubject(), $emailBody, $sender);
        $this->mailService->setTo(array($contactEmail));
        $this->mailService->setSubject($content->getSubject());
        $this->mailService->setFrom($sender, 'MyCasaParticular.com');
        $this->mailService->setBody($emailBody->getContent());
        $this->mailService->setEmailType($newsletterCode);

        return $this->mailService->sendEmail();
    }

}


