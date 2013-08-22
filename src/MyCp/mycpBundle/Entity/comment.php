<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(name="com_user", referencedColumnName="user_id", nullable=true)
     */
    private $com_user;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="ownership")
     * @ORM\JoinColumn(name="com_ownership", referencedColumnName="own_id")
     */
    private $com_ownership;
    
    /**
     * @var string
     *
     * @ORM\Column(name="com_username", type="string", nullable=true)
     */
    private $com_username;  
    
     /**
     * @var string
     *
     * @ORM\Column(name="com_email", type="string", nullable=true)
     */
    private $com_email;  

    /**
     * @var datetime
     *
     * @ORM\Column(name="com_date", type="datetime")
     */
    private $com_date;

    /**
     * @var integer
     *
     * @ORM\Column(name="com_rate", type="integer")
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
     * @ORM\Column(name="com_comments", type="string", length=800)
     */
    private $com_comments;
    
    
    /**
     * Get com_id
     *
     * @return integer 
     */
    public function getCommentId()
    {
        return $this->com_id;
    }

    /**
     * Set com_user
     *
     * @param \MyCp\mycpBundle\Entity\user $comUser
     * @return comment
     */
    public function setCommentUser($comUser)
    {
        $this->com_user = $comUser;
    
        return $this;
    }

    /**
     * Get com_user
     *
     * @return \MyCp\mycpBundle\Entity\user 
     */
    public function getCommentUser()
    {
        return $this->com_user;
    }
    
    /**
     * Set com_ownership
     *
     * @param \MyCp\mycpBundle\Entity\ownership $comOwnership
     * @return comment
     */
    public function setCommentOwnership($comOwnership)
    {
        $this->com_ownership = $comOwnership;
    
        return $this;
    }

    /**
     * Get com_ownership
     *
     * @return \MyCp\mycpBundle\Entity\ownership 
     */
    public function getCommentOwnership()
    {
        return $this->com_ownership;
    }
    
    
    /**
     * Set com_username
     *
     * @param string $comUserName
     * @return comment
     */
    public function setCommentUserName($comUserName)
    {
        $this->com_username = $comUserName;
    
        return $this;
    }

    /**
     * Get com_username
     *
     * @return string 
     */
    public function getCommentUserName()
    {
        return $this->com_username;
    }
    
    
    /**
     * Set com_date
     *
     * @param datetime $comDate
     * @return comment
     */
    public function setCommentDate($comDate)
    {
        $this->com_date = $comDate;
    
        return $this;
    }

    /**
     * Get com_date
     *
     * @return datetime 
     */
    public function getCommentDate()
    {
        return $this->com_date;
    }
    
    /**
     * Set com_rate
     *
     * @param integer $comRate
     * @return comment
     */
    public function setCommentRating($comRate)
    {
        $this->com_rate = $comRate;
    
        return $this;
    }

    /**
     * Get com_rate
     *
     * @return integer 
     */
    public function getCommentRating()
    {
        return $this->com_rate;
    }
    
    
    /**
     * Set com_comment
     *
     * @param string $comComment
     * @return comment
     */
    public function setComments($comComment)
    {
        $this->com_comments = $comComment;
    
        return $this;
    }

    /**
     * Get com_comment
     *
     * @return string 
     */
    public function getComments()
    {
        return $this->com_comments;
    }
    
    
    /**
     * Set com_email
     *
     * @param string $comEmail
     * @return comment
     */
    public function setEmail($comEmail)
    {
        $this->com_email = $comEmail;
    
        return $this;
    }

    /**
     * Get com_email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->com_email;
    }
    
    /**
     * Set com_public
     *
     * @param string $comPublic
     * @return comment
     */
    public function setPublic($comPublic)
    {
        $this->com_public = $comPublic;
    
        return $this;
    }

    /**
     * Get com_public
     *
     * @return string 
     */
    public function getPublic()
    {
        return $this->com_public;
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
     * Set com_username
     *
     * @param string $comUsername
     * @return comment
     */
    public function setComUsername($comUsername)
    {
        $this->com_username = $comUsername;
    
        return $this;
    }

    /**
     * Get com_username
     *
     * @return string 
     */
    public function getComUsername()
    {
        return $this->com_username;
    }

    /**
     * Set com_email
     *
     * @param string $comEmail
     * @return comment
     */
    public function setComEmail($comEmail)
    {
        $this->com_email = $comEmail;
    
        return $this;
    }

    /**
     * Get com_email
     *
     * @return string 
     */
    public function getComEmail()
    {
        return $this->com_email;
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
}