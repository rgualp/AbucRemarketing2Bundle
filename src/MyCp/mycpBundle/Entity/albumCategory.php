<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * albumCategory
 *
 * @ORM\Table(name="albumcategory")
 * @ORM\Entity
 */
class albumCategory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="alb_cat_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $alb_cat_id;

    /**
     * @ORM\OneToMany(targetEntity="albumCategoryLang",mappedBy="album_cat_id_cat")
     */
    private $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get alb_cat_id
     *
     * @return integer 
     */
    public function getAlbCatId()
    {
        return $this->alb_cat_id;
    }

    /*Logs functions*/
    public function getLogDescription()
    {
        return (count($this->translations) > 0) ? "Categoría de álbum ".$this->translations[0]->getAlbumCatDescription(). " y sus traducciones." : "Categoría de álbum con id ".$this->getAlbCatId()." y sus traducciones.";
    }
}