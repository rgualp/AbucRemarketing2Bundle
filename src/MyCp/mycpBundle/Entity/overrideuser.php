<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * message
 *
 * @ORM\Table(name="override_user")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\overrideUserRepository")
 *
 */
class overrideuser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="override_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $override_id;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="string", length=400)
     */
    private $reason;

    /**
     * @var string
     *
     * @ORM\Column(name="override_password", type="string", length=255)
     */
    private $override_password;

    /**
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(name="override_to",referencedColumnName="user_id")
     */
    private $override_to;

    /**
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(name="override_by",referencedColumnName="user_id")
     */
    private $override_by;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="override_date", type="datetime")
     */
    private $override_date;

    /**
     * @var boolean
     *
     * @ORM\Column(name="override_enable", type="boolean")
     */
    private $override_enable;

    /**
     * Get override_id
     *
     * @return integer
     */
    public function getOverrideId()
    {
        return $this->override_id;
    }

    /**
     * Set reason
     *
     * @param string $reason
     * @return overrideuser
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set override_to
     *
     * @param user $overrideTo
     * @return overrideuser
     */
    public function setOverrideTo(\MyCp\mycpBundle\Entity\user $overrideTo)
    {
        $this->override_to = $overrideTo;

        return $this;
    }

    /**
     * Get override_to
     *
     * @return \MyCp\mycpBundle\Entity\user
     */
    public function getOverrideTo()
    {
        return $this->override_to;
    }

    /**
     * Set override_by
     *
     * @param \MyCp\mycpBundle\Entity\user $overrideBy
     * @return overrideuser
     */
    public function setOverrideBy(\MyCp\mycpBundle\Entity\user $overrideBy)
    {
        $this->override_by = $overrideBy;

        return $this;
    }

    /**
     * Get override_by
     *
     * @return \MyCp\mycpBundle\Entity\user
     */
    public function getOverrideBy()
    {
        return $this->override_by;
    }

    /**
     * Set message_date
     *
     * @param DateTime $overrideDate
     * @return overrideuser
     */
    public function setOverrideDate($overrideDate)
    {
        $this->override_date = $overrideDate;

        return $this;
    }

    /**
     * Get override_date
     *
     * @return DateTime
     */
    public function getOverrideDate()
    {
        return $this->override_date;
    }

    /**
     * Set override_password
     *
     * @param string $overridePassword
     * @return overrideuser
     */
    public function setOverridePassword($overridePassword)
    {
        $this->override_password = $overridePassword;

        return $this;
    }

    /**
     * Get override_password
     *
     * @return string
     */
    public function getOverridePassword()
    {
        return $this->override_password;
    }

    /**
     * Set override_enable
     *
     * @param boolean $overrideEnable
     * @return overrideuser
     */
    public function setOverrideEnable($overrideEnable)
    {
        $this->override_enable = $overrideEnable;

        return $this;
    }

    /**
     * Get override_enable
     *
     * @return boolean
     */
    public function getOverrideEnable()
    {
        return $this->override_enable;
    }
}