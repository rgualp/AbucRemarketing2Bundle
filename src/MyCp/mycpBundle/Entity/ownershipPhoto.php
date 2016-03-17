<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipPhoto
 *
 * @ORM\Table(name="ownershipphoto")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\ownershipPhotoRepository")
 */
class ownershipPhoto
{
    /**
     * @var integer
     *
     * @ORM\Column(name="own_pho_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $own_pho_id;

    /**
     * @ORM\ManyToOne(targetEntity="photo",inversedBy="")
     * @ORM\JoinColumn(name="own_pho_pho_id",referencedColumnName="pho_id")
     */
    private $own_pho_photo;

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="photos")
     * @ORM\JoinColumn(name="own_pho_own_id",referencedColumnName="own_id")
     */
    private $own_pho_own;


    /**
     * Get own_pho_id
     *
     * @return integer 
     */
    public function getOwnPhoId()
    {
        return $this->own_pho_id;
    }

    /**
     * Set own_pho_photo
     *
     * @param \MyCp\mycpBundle\Entity\photo $ownPhoPhoto
     * @return ownershipPhoto
     */
    public function setOwnPhoPhoto(\MyCp\mycpBundle\Entity\photo $ownPhoPhoto = null)
    {
        $this->own_pho_photo = $ownPhoPhoto;
    
        return $this;
    }

    /**
     * Get own_pho_photo
     *
     * @return \MyCp\mycpBundle\Entity\photo 
     */
    public function getOwnPhoPhoto()
    {
        return $this->own_pho_photo;
    }

    /**
     * Set own_pho_own
     *
     * @param \MyCp\mycpBundle\Entity\ownership $ownPhoOwn
     * @return ownershipPhoto
     */
    public function setOwnPhoOwn(\MyCp\mycpBundle\Entity\ownership $ownPhoOwn = null)
    {
        $this->own_pho_own = $ownPhoOwn;
    
        return $this;
    }

    /**
     * Get own_pho_own
     *
     * @return \MyCp\mycpBundle\Entity\ownership 
     */
    public function getOwnPhoOwn()
    {
        return $this->own_pho_own;
    }
}