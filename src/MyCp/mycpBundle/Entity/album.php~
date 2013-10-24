<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * album
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\albumRepository")
 *
 */
class album
{
    /**
     * @var integer
     *
     * @ORM\Column(name="album_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $album_id;

    /**
     * @var string
     *
     * @ORM\Column(name="album_name", type="string", length=255)
     */
    private $album_name;

    /**
     * @var integer
     *
     * @ORM\Column(name="album_order", type="integer")
     */
    private $album_order;

    /**
     * @var boolean
     *
     * @ORM\Column(name="album_active", type="boolean")
     */
    private $album_active;


    /**
     * @ORM\ManyToOne(targetEntity="albumCategory",inversedBy="")
     * @ORM\JoinColumn(name="album_alb_cat_id",referencedColumnName="alb_cat_id")
     */
    private $album_category;


    

    /**
     * Get album_id
     *
     * @return integer 
     */
    public function getAlbumId()
    {
        return $this->album_id;
    }

    /**
     * Set album_name
     *
     * @param string $albumName
     * @return album
     */
    public function setAlbumName($albumName)
    {
        $this->album_name = $albumName;
    
        return $this;
    }

    /**
     * Get album_name
     *
     * @return string 
     */
    public function getAlbumName()
    {
        return $this->album_name;
    }

    /**
     * Set album_order
     *
     * @param integer $albumOrder
     * @return album
     */
    public function setAlbumOrder($albumOrder)
    {
        $this->album_order = $albumOrder;
    
        return $this;
    }

    /**
     * Get album_order
     *
     * @return integer 
     */
    public function getAlbumOrder()
    {
        return $this->album_order;
    }

    /**
     * Set album_active
     *
     * @param boolean $albumActive
     * @return album
     */
    public function setAlbumActive($albumActive)
    {
        $this->album_active = $albumActive;
    
        return $this;
    }

    /**
     * Get album_active
     *
     * @return boolean 
     */
    public function getAlbumActive()
    {
        return $this->album_active;
    }

    /**
     * Set album_category
     *
     * @param \MyCp\mycpBundle\Entity\albumCategory $albumCategory
     * @return album
     */
    public function setAlbumCategory(\MyCp\mycpBundle\Entity\albumCategory $albumCategory = null)
    {
        $this->album_category = $albumCategory;
    
        return $this;
    }

    /**
     * Get album_category
     *
     * @return \MyCp\mycpBundle\Entity\albumCategory 
     */
    public function getAlbumCategory()
    {
        return $this->album_category;
    }
}