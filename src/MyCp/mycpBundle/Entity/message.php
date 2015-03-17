<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * message
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\messageRepository")
 *
 */
class message
{
    /**
     * @var integer
     *
     * @ORM\Column(name="message_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $message_id;

    /**
     * @var string
     *
     * @ORM\Column(name="message_subject", type="string", length=400)
     */
    private $message_subject;

    /**
     * @var string
     *
     * @ORM\Column(name="mesage_body", type="text")
     */
    private $mesage_body;

    /**
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(name="message_send_to",referencedColumnName="user_id")
     */
    private $message_send_to;

    /**
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(name="message_sender",referencedColumnName="user_id")
     */
    private $message_sender;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="message_date", type="datetime")
     */
    private $message_date;




    /**
     * Get message_id
     *
     * @return integer
     */
    public function getMessageId()
    {
        return $this->message_id;
    }

    /**
     * Set message_subject
     *
     * @param string $messageSubject
     * @return message
     */
    public function setMessageSubject($messageSubject)
    {
        $this->message_subject = $messageSubject;

        return $this;
    }

    /**
     * Get message_subject
     *
     * @return string
     */
    public function getMessageSubject()
    {
        return $this->message_subject;
    }

    /**
     * Set message_body
     *
     * @param text $messageBody
     * @return message
     */
    public function setMessageBody($messageBody)
    {
        $this->message_body = $messageBody;

        return $this;
    }

    /**
     * Get message_body
     *
     * @return text
     */
    public function getMessageBody()
    {
        return $this->message_body;
    }

    /**
     * Set message_send_to
     *
     * @param user $messageSendTo
     * @return message
     */
    public function setMessageSendTo(\MyCp\mycpBundle\Entity\user $messageSendTo)
    {
        $this->message_send_to = $messageSendTo;

        return $this;
    }

    /**
     * Get message_send_to
     *
     * @return \MyCp\mycpBundle\Entity\user
     */
    public function getMessageSendTo()
    {
        return $this->message_send_to;
    }

    /**
     * Set message_sender
     *
     * @param \MyCp\mycpBundle\Entity\user $messageSender
     * @return message
     */
    public function setMessageSender(\MyCp\mycpBundle\Entity\user $messageSender)
    {
        $this->message_sender = $messageSender;

        return $this;
    }

    /**
     * Get message_sender
     *
     * @return \MyCp\mycpBundle\Entity\user
     */
    public function getMessageSender()
    {
        return $this->message_sender;
    }

    /**
     * Set message_date
     *
     * @param DateTime $messageDate
     * @return message
     */
    public function setMessageDate(DateTime $messageDate)
    {
        $this->message_date = $messageDate;

        return $this;
    }

    /**
     * Get message_date
     *
     * @return DateTime
     */
    public function getMessageDate()
    {
        return $this->message_date;
    }
}