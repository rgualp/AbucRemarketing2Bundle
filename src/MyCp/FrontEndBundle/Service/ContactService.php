<?php

namespace MyCp\FrontEndBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ContactService extends Controller
{
    /**
     * @var string
     */
    private $additionalFilesPath;

    public function __construct($additionalFilesPath)
    {
        $this->additionalFilesPath = $additionalFilesPath;
    }

    /**
     * @param $userLocale
     * @param $userEmail
     * @param $userFullName
     */
    public function sendInstructionsEmail($userLocale, $userEmail, $userFullName)
    {
        $emailService = $this->get('Email');
        $logger = $this->get('logger');

        $body = $this->render('FrontEndBundle:mails:contact_instructions_mail.html.twig', array(
            'user_locale' => $userLocale,
            'userFullName' => $userFullName
         ));

        $locale = $this->get('translator');
        $subject = $locale->trans('INSTRUCTIONS_CONTACT_SUBJECT', array(), "messages", $userLocale);
        $instructionsFile = $this->getInstructionsZipPath();

        try {
            $emailService->sendEmail(
                $subject,
                'casa@mycasaparticular.com',
                $subject . ' - MyCasaParticular.com',
                $userEmail,
                $body,
                $instructionsFile
            );

            $logger->info('Successfully sent email to user ' . $userEmail );
            $message = $this->get('translator')->trans("SEND_INSTRUCTIONS_MESSAGE_SUCCESS");
            $this->get('session')->getFlashBag()->add('message_global_success', $message);

        } catch (\Exception $e) {
            $logger->error(sprintf(
                'EMAIL: Could not send Email to User. Email: %s',
                $userEmail));
            $logger->error($e->getMessage());
        }

    }

    public function sendOwnerContactToTeam($ownerFullName, $ownerOwnName, $ownerProvince, $ownerMunicipality, $ownerComment, $ownerEmail, $ownerPhone){
        $service_email = $this->get('Email');

        if($ownerComment === "" || $ownerComment === null)
        {
            $message = $this->get('translator')->trans("REQUIRED_FIELDS_NEXT_STEP");
            $this->get('session')->getFlashBag()->add('message_global_error', $message);
        }
        else {
            $content = $this->render('FrontEndBundle:mails:ownerContactMailBody.html.twig', array(
                'owner_fullname' => $ownerFullName,
                'own_name' => $ownerOwnName,
                'province' => $ownerProvince,
                'municipality' => $ownerMunicipality,
                'comments' => $ownerComment,
                'email' => $ownerEmail,
                'phone' => $ownerPhone
            ));
            $service_email->sendTemplatedEmail(
                'Contacto de propietario', $ownerEmail, 'casa@mycasaparticular.com', $content->getContent());

            $message = $this->get('translator')->trans("USER_CONTACT_OWNER_SUCCESS");
            $this->get('session')->getFlashBag()->add('message_global_success', $message);
        }
    }

    public function sendTouristContact($touristName, $touristLastName, $touristPhone, $touristEmail, $touristComment)
    {
        $service_email = $this->get('Email');
        $content = $this->render('FrontEndBundle:mails:contactMailBody.html.twig', array(
            'tourist_name' => $touristName,
            'tourist_last_name' => $touristLastName,
            'tourist_phone' => $touristPhone,
            'tourist_email' => $touristEmail,
            'tourist_comment' => $touristComment
        ));
        $service_email->sendTemplatedEmail(
            'Contacto de huesped', $touristEmail, 'info@mycasaparticular.com ', $content->getContent());
        $message = $this->get('translator')->trans("USER_CONTACT_TOURIST_SUCCESS");
        $this->get('session')->getFlashBag()->add('message_global_success', $message);
    }

    private function getInstructionsZipPath()
    {
        $filePath = $this->additionalFilesPath . "MyCasaParticular.zip";
        return $filePath;
    }

}
