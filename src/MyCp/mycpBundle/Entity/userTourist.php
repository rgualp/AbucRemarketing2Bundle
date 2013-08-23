<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * usertourist
 *
 * @ORM\Table(name="usertourist")
 * @ORM\Entity
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
     * Get user_tourist_id
     *
     * @return integer 
     */
    public function getUserTouristId()
    {
        return $this->user_tourist_id;
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