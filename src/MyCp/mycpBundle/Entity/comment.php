<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * comment
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\commentRepository")
 */
class comment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="com_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $com_id;

    /**
     * @ORM\ManyToOne(targetEntity="user", inversedBy="comments")
     * @ORM\JoinColumn(name="com_user", referencedColumnName="user_id", nullable=true)
     * @ORM\OrderBy({"user_user_name" = "ASC", "user_last_name" = "ASC"})
     */
    private $com_user;

    private $com_user_email;
    
    /**
     * @ORM\ManyToOne(targetEntity="ownership", inversedBy="comments")
     * @ORM\JoinColumn(name="com_ownership", referencedColumnName="own_id")
     * @ORM\OrderBy({"own_mcp_code" = "ASC", "own_name" = "ASC"})
     */
    private $com_ownership;

    private $com_ownership_code;

    /**
     * @var datetime
     *
     * @ORM\Column(name="com_date", type="datetime", nullable=true)
     */
    private $com_date;

    /**
     * @var integer
     *
     * @ORM\Column(name="com_rate", type="integer")
     * @Assert\NotBlank()
     */
    private $com_rate;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="com_public", type="boolean")
     */
    private $com_public;

    /**
     * @var string
     *
     * @ORM\Column(name="com_comments", type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min=6)
     */
    private $com_comments;

    /**
     * @var boolean
     *
     * @ORM\Column(name="positive", type="boolean", nullable= true)
     */
    private $positive;

    /**
     * @var boolean
     *
     * @ORM\Column(name="com_by_client", type="boolean")
     */
    private $com_by_client;

    public function __construct()
    {
        $this->setPositive(false);
    }

    /**
     * Get com_id
     *
     * @return integer 
     */
    public function getComId()
    {
        return $this->com_id;
    }

    /**
     * Set com_date
     *
     * @param \DateTime $comDate
     * @return comment
     */
    public function setComDate($comDate)
    {
        $this->com_date = $comDate;
    
        return $this;
    }

    /**
     * Get com_date
     *
     * @return \DateTime 
     */
    public function getComDate()
    {
        return $this->com_date;
    }

    /**
     * Set com_rate
     *
     * @param integer $comRate
     * @return comment
     */
    public function setComRate($comRate)
    {
        $this->com_rate = $comRate;
    
        return $this;
    }

    /**
     * Get com_rate
     *
     * @return integer 
     */
    public function getComRate()
    {
        return $this->com_rate;
    }

    /**
     * Set com_public
     *
     * @param boolean $comPublic
     * @return comment
     */
    public function setComPublic($comPublic)
    {
        $this->com_public = $comPublic;
    
        return $this;
    }

    /**
     * Get com_public
     *
     * @return boolean
     */
    public function getComPublic()
    {
        return $this->com_public;
    }

    /**
     * Set com_comments
     *
     * @param string $comComments
     * @return comment
     */
    public function setComComments($comComments)
    {
        $this->com_comments = $comComments;
    
        return $this;
    }

    /**
     * Get com_comments
     *
     * @return string 
     */
    public function getComComments()
    {
        return $this->com_comments;
    }

    /**
     * Set com_user
     *
     * @param \MyCp\mycpBundle\Entity\user $comUser
     * @return comment
     */
    public function setComUser(\MyCp\mycpBundle\Entity\user $comUser = null)
    {
        $this->com_user = $comUser;
    
        return $this;
    }

    /**
     * Get com_user
     *
     * @return \MyCp\mycpBundle\Entity\user 
     */
    public function getComUser()
    {
        return $this->com_user;
    }

    /**
     * Set com_ownership
     *
     * @param \MyCp\mycpBundle\Entity\ownership $comOwnership
     * @return comment
     */
    public function setComOwnership(\MyCp\mycpBundle\Entity\ownership $comOwnership = null)
    {
        $this->com_ownership = $comOwnership;
    
        return $this;
    }

    /**
     * Get com_ownership
     *
     * @return \MyCp\mycpBundle\Entity\ownership 
     */
    public function getComOwnership()
    {
        return $this->com_ownership;
    }

    /*Logs functions*/
    public function getLogDescription()
    {
        return "Comentario realizado ".$this->getComDate()->format("d/m/Y")." en alojamiento ".$this->getComOwnership()->getOwnMcpCode()." por ".$this->getComUser()->getUserCompleteName();
    }

    /**
     * @return boolean
     */
    public function isPositive()
    {
        return $this->positive;
    }

    /**
     * @param boolean $positive
     * @return mixed
     */
    public function setPositive($positive)
    {
        $this->positive = $positive;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComUserEmail()
    {
        return $this->com_user_email;
    }

    /**
     * @param mixed $com_user_email
     */
    public function setComUserEmail($com_user_email)
    {
        $this->com_user_email = $com_user_email;
    }

    /**
     * @return mixed
     */
    public function getComOwnershipCode()
    {
        return $this->com_ownership_code;
    }

    /**
     * @param mixed $com_ownership_code
     */
    public function setComOwnershipCode($com_ownership_code)
    {
        $this->com_ownership_code = $com_ownership_code;
    }



    /**
     * Get positive
     *
     * @return boolean
     */
    public function getPositive()
    {
        return $this->positive;
    }

    /**
     * Set comByClient
     *
     * @param boolean $comByClient
     *
     * @return comment
     */
    public function setComByClient($comByClient)
    {
        $this->com_by_client = $comByClient;

        return $this;
    }

    /**
     * Get comByClient
     *
     * @return boolean
     */
    public function getComByClient()
    {
        return $this->com_by_client;
    }
}
