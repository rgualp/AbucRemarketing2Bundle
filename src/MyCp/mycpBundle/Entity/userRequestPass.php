<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * usercasa
 *
 * @ORM\Table(name="user_request_pass")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\userCasaRepository")
 */
class userRequestPass
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
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(name="user",referencedColumnName="user_id")
     */
    private $user;

     /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;


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
     * Set code
     *
     * @param string $code
     *
     * @return userRequestPass
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set user
     *
     * @param \MyCp\mycpBundle\Entity\user $user
     *
     * @return userRequestPass
     */
    public function setUser(\MyCp\mycpBundle\Entity\user $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \MyCp\mycpBundle\Entity\user
     */
    public function getUser()
    {
        return $this->user;
    }
}
