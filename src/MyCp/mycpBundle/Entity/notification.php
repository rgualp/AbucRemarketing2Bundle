<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * notification
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\notificationsRepository")
 *
 */
class notification
{
    const SUB_TYPE_CHECKIN = "CHECKIN";
    const SUB_TYPE_INMEDIATE_BOOKING = "INMEDIATE_BOOKING";
    const SUB_TYPE_RESERVATION_PAID = "RESERVATION_PAID";
    const SUB_TYPE_CANCELED_BOOKING = "CANCELED_BOOKING";
    const SUB_TYPE_COMPLETE_PAYMENT = "COMPLETE_PAYMENT";
    const SUB_TYPE_COMPLETE_PAYMENT_DEPOSIT = "COMPLETE_PAYMENT_DEPOSIT";
    const SUB_TYPE_COMMENT_OWN = "COMMENT_OWN";

    //todo. Adicionar la accion de vencida e implementar el comando
    const ACTION_RESPONSE_CLOSE = "CLOSE";
    const ACTION_RESPONSE_AVAILABLE = "AVAILABLE";
    const ACTION_RESPONSE_UNAVAILABLE = "UNAVAILABLE";

    const STATUS_NEW = 0;
    const STATUS_READ = 1;
    const STATUS_DISCARDED = 2;

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
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
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
     * @ORM\ManyToOne(targetEntity="generalReservation",inversedBy="notifications")
     * @ORM\JoinColumn(name="reservation",referencedColumnName="gen_res_id")
     */
    private $reservation;

    /**
     * @var string
     *
     * @ORM\Column(name="actionResponse",  type="string", length=255, nullable=true)
     */
    private $actionResponse;

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="notifications")
     * @ORM\JoinColumn(name="id_ownership",referencedColumnName="own_id")
     */
    private $ownership;

    /**
     * @var boolean
     *
     * @ORM\Column(name="sync", type="boolean", nullable=true)
     */
    private $sync;

    /**
     * Constructor
     */
    public function __construct() {
        $this->status = notification::STATUS_NEW;
    }

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
     * @return string
     */
    public function getStringSubtype()
    {
        switch($this->subtype){
            case notification::SUB_TYPE_CHECKIN:
                return "CHECKIN";
            case notification::SUB_TYPE_INMEDIATE_BOOKING:
                return "SOLICITUD DE DISPONIBILIDAD";
            case notification::SUB_TYPE_RESERVATION_PAID:
                return "RESERVACIÓN PAGADA";
            case notification::SUB_TYPE_CANCELED_BOOKING:
                return "RESERVACIÓN CANCELADA";
            case notification::SUB_TYPE_COMPLETE_PAYMENT:
                return "SOLICITUD DE AGENCIA";
            case notification::SUB_TYPE_COMPLETE_PAYMENT_DEPOSIT:
                return "PAGO POR RESERVA DE AGENCIA";
            case notification::SUB_TYPE_COMMENT_OWN:
                return "COMENTARIO DE CLIENTE ";
        }
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

    /**
     * @return mixed
     */
    public function getReservation()
    {
        return $this->reservation;
    }

    /**
     * @param mixed $reservation
     * @return mixed
     */
    public function setReservation($reservation)
    {
        $this->reservation = $reservation;
        return $this;
    }

    /**
     * Set actionResponse
     *
     * @param string $actionResponse
     *
     * @return notification
     */
    public function setActionResponse($actionResponse)
    {
        $this->actionResponse = $actionResponse;
        $this->setStatus(notification::STATUS_READ);

        return $this;
    }

    /**
     * Get actionResponse
     *
     * @return string
     */
    public function getActionResponse()
    {
        return $this->actionResponse;
    }

    /**
     * @return string
     */
    public function getStringActionResponse()
    {
        switch($this->actionResponse){
            case notification::ACTION_RESPONSE_AVAILABLE:
                return "Disponible";
            case notification::ACTION_RESPONSE_UNAVAILABLE:
                return "NO Disponible";
            case notification::ACTION_RESPONSE_CLOSE:
                return "Cerrada";
        }
        return $this->subtype;
    }

    /**
     * Set ownership
     *
     * @param \MyCp\mycpBundle\Entity\ownership $ownership
     *
     * @return notification
     */
    public function setOwnership(\MyCp\mycpBundle\Entity\ownership $ownership = null)
    {
        $this->ownership = $ownership;

        return $this;
    }

    /**
     * Get ownership
     *
     * @return \MyCp\mycpBundle\Entity\ownership
     */
    public function getOwnership()
    {
        return $this->ownership;
    }

    /**
     * Set sync
     *
     * @param boolean $sync
     *
     * @return notification
     */
    public function setSync($sync)
    {
        $this->sync = $sync;

        return $this;
    }

    /**
     * Get sync
     *
     * @return boolean
     */
    public function getSync()
    {
        return $this->sync;
    }
}
