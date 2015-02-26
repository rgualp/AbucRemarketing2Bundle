<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * usertourist
 *
 * @ORM\Table(name="usertourist")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\userTouristRepository")
 */
class userTourist
{
    /**
     * @var integer
     *
     * @ORM\Column(name="user_tourist_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $user_tourist_id;

    /**
     * @ORM\ManyToOne(targetEntity="currency",inversedBy="")
     * @ORM\JoinColumn(name="user_tourist_currency",referencedColumnName="curr_id")
     */
    private $user_tourist_currency;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="")
     * @ORM\JoinColumn(name="user_tourist_language",referencedColumnName="lang_id")
     */
    private $user_tourist_language;

    /**
     * @ORM\ManyToOne(targetEntity="user",inversedBy="")
     * @ORM\JoinColumn(name="user_tourist_user",referencedColumnName="user_id")
     */
    private $user_tourist_user;

    /**
     * @var string
     *
     * @ORM\Column(name="user_tourist_gender", type="string", length=255, nullable=true)
     */
    private $user_tourist_gender;

     /**
     * @var string
     *
     * @ORM\Column(name="user_tourist_postal_code", type="string", length=255, nullable=true)
     */
    private $user_tourist_postal_code;

    /**
     * @var string
     *
     * @ORM\Column(name="user_tourist_cell", type="string", length=255, nullable=true)
     */
    private $user_tourist_cell;


    /**
     * Get user_tourist_id
     *
     * @return integer
     */
    public function getUserTouristId()
    {
        return $this->user_tourist_id;
    }

     /**
     * Set user_tourist_cell
     *
     * @param string $userTouristCell
     * @return userTourist
     */
    public function setUserTouristCell($userTouristCell = null)
    {
        $this->user_tourist_cell = $userTouristCell;

        return $this;
    }

    /**
     * Get user_tourist_cell
     *
     * @return varchar
     */
    public function getUserTouristCell()
    {
        return $this->user_tourist_cell;
    }

    /**
     * Set user_tourist_postal_code
     *
     * @param string $userTouristPostalCode
     * @return userTourist
     */
    public function setUserTouristPostalCode($userTouristPostalCode = null)
    {
        $this->user_tourist_postal_code = $userTouristPostalCode;

        return $this;
    }

    /**
     * Get user_tourist_postal_code
     *
     * @return varchar
     */
    public function getUserTouristPostalCode()
    {
        return $this->user_tourist_postal_code;
    }

    /**
     * Set user_tourist_gender
     *
     * @param string $userTouristGender
     * @return userTourist
     */
    public function setUserTouristGender($userTouristGender = null)
    {
        $this->user_tourist_gender = $userTouristGender;

        return $this;
    }

    /**
     * Get user_tourist_gender
     *
     * @return varchar
     */
    public function getUserTouristGender()
    {
        return $this->user_tourist_gender;
    }

    /**
     * Set user_tourist_currency
     *
     * @param \MyCp\mycpBundle\Entity\currency $userTouristCurrency
     * @return userTourist
     */
    public function setUserTouristCurrency(\MyCp\mycpBundle\Entity\currency $userTouristCurrency = null)
    {
        $this->user_tourist_currency = $userTouristCurrency;

        return $this;
    }

    /**
     * Get user_tourist_currency
     *
     * @return \MyCp\mycpBundle\Entity\currency
     */
    public function getUserTouristCurrency()
    {
        return $this->user_tourist_currency;
    }

    /**
     * Set user_tourist_language
     *
     * @param \MyCp\mycpBundle\Entity\lang $userTouristLanguage
     * @return userTourist
     */
    public function setUserTouristLanguage(\MyCp\mycpBundle\Entity\lang $userTouristLanguage = null)
    {
        $this->user_tourist_language = $userTouristLanguage;

        return $this;
    }

    /**
     * Get user_tourist_language
     *
     * @return \MyCp\mycpBundle\Entity\lang
     */
    public function getUserTouristLanguage()
    {
        return $this->user_tourist_language;
    }

    /**
     * Set user_tourist_user
     *
     * @param \MyCp\mycpBundle\Entity\user $userTouristUser
     * @return userTourist
     */
    public function setUserTouristUser(\MyCp\mycpBundle\Entity\user $userTouristUser = null)
    {
        $this->user_tourist_user = $userTouristUser;

        return $this;
    }

    /**
     * Get user_tourist_user
     *
     * @return \MyCp\mycpBundle\Entity\user
     */
    public function getUserTouristUser()
    {
        return $this->user_tourist_user;
    }
}