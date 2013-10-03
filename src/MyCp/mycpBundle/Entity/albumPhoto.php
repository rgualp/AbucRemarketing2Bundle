<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * albumPhoto
 *
 * @ORM\Table(name="albumphoto")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\albumPhotoRepository")
 */
class albumPhoto
{
    /**
     * @var integer
     *
     * @ORM\Column(name="alb_pho_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $alb_pho_id;

    /**
     * @ORM\ManyToOne(targetEntity="photo",inversedBy="")
     * @ORM\JoinColumn(name="alb_pho_pho_id",referencedColumnName="pho_id")
     */
    private $alb_pho_photo;

    /**
     * @ORM\ManyToOne(targetEntity="album",inversedBy="")
     * @ORM\JoinColumn(name="alb_pho_alb_id",referencedColumnName="album_id")
     */
    private $alb_pho_album;

    /**
     * Get alb_pho_id
     *
     * @return integer 
     */
    public function getAlbPhoId()
    {
        return $this->alb_pho_id;
    }

    /**
     * Set alb_pho_photo
     *
     * @param \MyCp\mycpBundle\Entity\photo $albPhoPhoto
     * @return photo
     */
    public function setAlbPhoPhoto(\MyCp\mycpBundle\Entity\photo $albPhoPhoto = null)
    {
        $this->alb_pho_photo = $albPhoPhoto;
    
        return $this;
    }

    /**
     * Get alb_pho_photo
     *
     * @return \MyCp\mycpBundle\Entity\photo 
     */
    public function getAlbPhoPhoto()
    {
        return $this->alb_pho_photo;
    }

    /**
     * Set alb_pho_album
     *
     * @param \MyCp\mycpBundle\Entity\album $albPhoAlbum
     * @return albumPhoto
     */
    public function setAlbPhoAlbum(\MyCp\mycpBundle\Entity\album $albPhoAlbum = null)
    {
        $this->alb_pho_album = $albPhoAlbum;
    
        return $this;
    }

    /**
     * Get alb_pho_album
     *
     * @return \MyCp\mycpBundle\Entity\album 
     */
    public function getAlbPhoAlbum()
    {
        return $this->alb_pho_album;
    }
}