<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * messageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class messageRepository extends EntityRepository
{

    /**
     * Creating a new message sended from Reservation Team to the client
     * @param user $fromUser
     * @param user $toUser
     * @param $subject
     * @param $messageBody
     * @return mixed
     */
    public function insert(user $fromUser, user $toUser, $subject, $messageBody)
    {
        if($messageBody != "") {
            $em = $this->getEntityManager();

            $message = new message();
            $message->setMessageBody($messageBody);
            $message->setMessageDate(new \DateTime());
            $message->setMessageSendTo($toUser);
            $message->setMessageSender($fromUser);
            $message->setMessageSubject($subject);

            $em->persist($message);
            $em->flush();

            return $message;
        }
        return null;
    }
}
