<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * favorite
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\favoriteRepository")
 *
 */
class favorite
{
    /**
     * @var integer
     *
     * @ORM\Column(name="favorite_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $favorite_id;
    
    /**
     * @ORM\ManyToOne(targetEntity="user",inversedBy="")
     * @ORM\JoinColumn(name="favorite_user",referencedColumnName="user_id", nullable=true)
     */
    private $favorite_user;

    /**
     * @var string
     *
     * @ORM\Column(name="favorite_session_id", type="string", length=255, nullable=true)
     */
    private $favorite_session_id;
    
    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="")
     * @ORM\JoinColumn(name="favorite_ownership",referencedColumnName="own_id", nullable=true)
     */
    private $favorite_ownership;
    
    /**
     * @ORM\ManyToOne(targetEntity="destination",inversedBy="")
     * @ORM\JoinColumn(name="favorite_destination",referencedColumnName="des_id", nullable=true)
     */
    private $favorite_destination;
    
     /**
     * @var datetime
     *
     * @ORM\Column(name="favorite_creation_date", type="datetime", nullable=true)
     */
    private $favorite_creation_date;

    /**
     * Get favorite_id
     *
     * @return integer 
     */
    public function getFavoriteId()
    {
        return $this->favorite_id;
    }
    
    /**
     * Set favorite_creation_date
     *
     * @param \DateTime $value
     * @return favorite
     */
    public function setFavoriteCreationDate($value)
    {
        $this->favorite_creation_date = $value;
    
        return $this;
    }

    /**
     * Get favorite_creation_date
     *
     * @return \DateTime 
     */
    public function getFavoriteCreationDate()
    {
        return $this->favorite_creation_date;
    }

    /**
     * Set favorite_session_id
     *
     * @param string $value
     * @return favorite
     */
    public function setFavoriteSessionId($value = null)
    {
        if($value != null)
           $this->favorite_session_id = $value;
    
        return $this;
    }

    /**
     * Get favorite_session_id
     *
     * @return string 
     */
    public function getFavoriteSessionId()
    {
        return $this->favorite_session_id;
    }

    /**
     * Set favorite_ownership
     *
     * @param \MyCp\mycpBundle\Entity\ownership $value
     * @return favorite
     */
    public function setFavoriteOwnership(\MyCp\mycpBundle\Entity\ownership $value = null)
    {
        if($value != null)
           $this->favorite_ownership = $value;
    
        return $this;
    }

    /**
     * Get favorite_ownership
     *
     * @return \MyCp\mycpBundle\Entity\ownership 
     */
    public function getFavoriteOwnership()
    {
        return $this->favorite_ownership;
    }

    /**
     * Set favorite_destination
     *
     * @param \MyCp\mycpBundle\Entity\destination $value
     * @return favorite
     */
    public function setFavoriteDestination(\MyCp\mycpBundle\Entity\destination $value = null)
    {
        if($value != null)
           $this->favorite_destination = $value;
    
        return $this;
    }

    /**
     * Get favorite_destination
     *
     * @return \MyCp\mycpBundle\Entity\destination 
     */
    public function getFavoriteDestination()
    {
        return $this->favorite_destination;
    }

    /**
     * Set favorite_user
     *
     * @param \MyCp\mycpBundle\Entity\user $value
     * @return favorite
     */
    public function setFavoriteUser(\MyCp\mycpBundle\Entity\user $value = null)
    {
        if($value != null)
           $this->favorite_user = $value;
    
        return $this;
    }

    /**
     * Get favorite_user
     *
     * @return \MyCp\mycpBundle\Entity\user 
     */
    public function getFavoriteUser()
    {
        return $this->favorite_user;
    }
}