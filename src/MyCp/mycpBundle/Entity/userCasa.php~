<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * usercasa
 *
 * @ORM\Table(name="usercasa")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\userCasaRepository")
 */
class userCasa
{
    /**
     * @var integer
     *
     * @ORM\Column(name="user_casa_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $user_casa_id;

    /**
     * @ORM\ManyToOne(targetEntity="user",inversedBy="")
     * @ORM\JoinColumn(name="user_casa_user",referencedColumnName="user_id")
     */
    private $user_casa_user;

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="")
     * @ORM\JoinColumn(name="user_casa_ownership",referencedColumnName="own_id")
     */
    private $user_casa_ownership;



    /**
     * Get user_casa_id
     *
     * @return integer 
     */
    public function getUserCasaId()
    {
        return $this->user_casa_id;
    }

    /**
     * Set user_casa_user
     *
     * @param \MyCp\mycpBundle\Entity\user $userCasaUser
     * @return userCasa
     */
    public function setUserCasaUser(\MyCp\mycpBundle\Entity\user $userCasaUser = null)
    {
        $this->user_casa_user = $userCasaUser;
    
        return $this;
    }

    /**
     * Get user_casa_user
     *
     * @return \MyCp\mycpBundle\Entity\user 
     */
    public function getUserCasaUser()
    {
        return $this->user_casa_user;
    }

    /**
     * Set user_casa_ownership
     *
     * @param \MyCp\mycpBundle\Entity\ownership $userCasaOwnership
     * @return userCasa
     */
    public function setUserCasaOwnership(\MyCp\mycpBundle\Entity\ownership $userCasaOwnership = null)
    {
        $this->user_casa_ownership = $userCasaOwnership;
    
        return $this;
    }

    /**
     * Get user_casa_ownership
     *
     * @return \MyCp\mycpBundle\Entity\ownership 
     */
    public function getUserCasaOwnership()
    {
        return $this->user_casa_ownership;
    }
}