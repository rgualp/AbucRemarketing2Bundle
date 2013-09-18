<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * destinationPhoto
 *
 * @ORM\Table(name="destinationphoto")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\destinationPhotoRepository")
 */
class destinationPhoto
{
    /**
     * @var integer
     *
     * @ORM\Column(name="des_pho_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $des_pho_id;

    /**
     * @ORM\ManyToOne(targetEntity="photo",inversedBy="")
     * @ORM\JoinColumn(name="des_pho_pho_id",referencedColumnName="pho_id")
     */
    private $des_pho_photo;

    /**
     * @ORM\ManyToOne(targetEntity="destination",inversedBy="")
     * @ORM\JoinColumn(name="des_pho_des_id",referencedColumnName="des_id")
     */
    private $des_pho_destination;


    /**
     * Get des_pho_id
     *
     * @return integer 
     */
    public function getDesPhoId()
    {
        return $this->des_pho_id;
    }

    /**
     * Set des_pho_photo
     *
     * @param \MyCp\mycpBundle\Entity\photo $desPhoPhoto
     * @return destinationPhoto
     */
    public function setDesPhoPhoto(\MyCp\mycpBundle\Entity\photo $desPhoPhoto = null)
    {
        $this->des_pho_photo = $desPhoPhoto;
    
        return $this;
    }

    /**
     * Get des_pho_photo
     *
     * @return \MyCp\mycpBundle\Entity\photo 
     */
    public function getDesPhoPhoto()
    {
        return $this->des_pho_photo;
    }

    /**
     * Set des_pho_destination
     *
     * @param \MyCp\mycpBundle\Entity\destination $desPhoDestination
     * @return destinationPhoto
     */
    public function setDesPhoDestination(\MyCp\mycpBundle\Entity\destination $desPhoDestination = null)
    {
        $this->des_pho_destination = $desPhoDestination;
    
        return $this;
    }

    /**
     * Get des_pho_destination
     *
     * @return \MyCp\mycpBundle\Entity\destination 
     */
    public function getDesPhoDestination()
    {
        return $this->des_pho_destination;
    }
}