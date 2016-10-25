<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * newsletter
 *
 * @ORM\Table(name="newsletter")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\newsletterRepository")
 */
class newsletter
{
    const NEWSLETTER_TYPE_EMAIL = 1;
    const NEWSLETTER_TYPE_SMS = 2;

    private $types = array(
        self::NEWSLETTER_TYPE_EMAIL,
        self::NEWSLETTER_TYPE_SMS
    );

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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="usersRole", type="string", length=255)
     */
    private $usersRole;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10)
     */
    private $code;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creation_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="schedule_date", type="datetime", nullable=true)
     */
    private $schedule_date;

    /**
     * @var string
     *
     * @ORM\Column(name="sent", type="boolean")
     */
    private $sent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent_date", type="datetime", nullable=true)
     */
    private $sent_date;

    /**
     * @ORM\OneToMany(targetEntity="newsletterEmail",mappedBy="newsletter")
     */
    private $emails;

    /**
     * @ORM\OneToMany(targetEntity="newsletterContent",mappedBy="newsletter")
     */
    private $contents;

    public function __construct()
    {
        $this->creation_date = new \DateTime();
        $this->sent = false;
        $this->emails = new ArrayCollection();
        $this->contents = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creation_date;
    }

    /**
     * @param \DateTime $creation_date
     * @return mixed
     */
    public function setCreationDate($creation_date)
    {
        $this->creation_date = $creation_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * @param string $sent
     * @return mixed
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param mixed $emails
     * @return mixed
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @param mixed $contents
     * @return mixed
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getScheduleDate()
    {
        return $this->schedule_date;
    }

    /**
     * @param \DateTime $schedule_date
     * @return mixed
     */
    public function setScheduleDate($schedule_date)
    {
        $this->schedule_date = $schedule_date;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSentDate()
    {
        return $this->sent_date;
    }

    /**
     * @param \DateTime $sent_date
     * @return mixed
     */
    public function setSentDate($sent_date)
    {
        $this->sent_date = $sent_date;
        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return mixed
     */
    public function setType($type)
    {
        if (!in_array($type, $this->types)) {
            throw new \InvalidArgumentException("Newsletter type $type not allowed");
        }

        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsersRole()
    {
        return $this->usersRole;
    }

    /**
     * @param string $usersRole
     * @return mixed
     */
    public function setUsersRole($usersRole)
    {
        $this->usersRole = $usersRole;
        return $this;
    }



}
