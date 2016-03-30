<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * notification
 *
 * @ORM\Table()
 * @ORM\Entity
 *
 */
class notification
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="sendTo", type="string", length=255)
     */
    private $sendTo;

    /**
     * @var string
     *
     * @ORM\Column(name="message",  type="string", length=255)
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclator",inversedBy="")
     * @ORM\JoinColumn(name="notificationType",referencedColumnName="nom_id")
     */
    private $notificationType;

    /**
     * @var string
     *
     * @ORM\Column(name="subtype",  type="string", length=255)
     */
    private $subtype;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclator",inversedBy="")
     * @ORM\JoinColumn(name="status",referencedColumnName="nom_id")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="code",  type="string", length=255)
     */
    private $code;

    /**
     * @var text
     *
     * @ORM\Column(name="response",  type="text", nullable=true)
     */
    private $response;

    /**
     * @var datetime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var text
     *
     * @ORM\Column(name="description",  type="text", nullable=true)
     */
    private $description;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return mixed
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubtype()
    {
        return $this->subtype;
    }

    /**
     * @param string $subtype
     * @return mixed
     */
    public function setSubtype($subtype)
    {
        $this->subtype = $subtype;
        return $this;
    }

    /**
     * @return string
     */
    public function getSendTo()
    {
        return $this->sendTo;
    }

    /**
     * @param string $to
     * @return mixed
     */
    public function setSendTo($to)
    {
        $this->sendTo = $to;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotificationType()
    {
        return $this->notificationType;
    }

    /**
     * @param mixed $notificationType
     * @return mixed
     */
    public function setNotificationType($notificationType)
    {
        $this->notificationType = $notificationType;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param datetime $created
     * @return mixed
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return text
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param text $response
     * @return mixed
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return text
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param text $description
     * @return mixed
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }


}