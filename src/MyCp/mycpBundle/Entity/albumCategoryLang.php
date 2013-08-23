<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * albumCategoryLang
 *
 * @ORM\Table(name="albumcategorylang")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\albumCategoryLangRepository")
 */
class albumCategoryLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="album_cat_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $album_cat_id;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="albumCatLang")
     * @ORM\JoinColumn(name="album_cat_id_lang",referencedColumnName="lang_id")
     */
    private $album_cat_id_lang;

    /**
     * @var string
     *
     * @ORM\Column(name="album_cat_description", type="string", length=255)
     */
    private $album_cat_description;

    /**
     * @ORM\ManyToOne(targetEntity="albumCategory",inversedBy="")
     * @ORM\JoinColumn(name="album_cat_id_cat",referencedColumnName="alb_cat_id")
     */
    private $album_cat_id_cat;


    /**
     * Get album_cat_id
     *
     * @return integer 
     */
    public function getAlbumCatId()
    {
        return $this->album_cat_id;
    }

    /**
     * Set album_cat_description
     *
     * @param string $albumCatDescription
     * @return albumCategoryLang
     */
    public function setAlbumCatDescription($albumCatDescription)
    {
        $this->album_cat_description = $albumCatDescription;
    
        return $this;
    }

    /**
     * Get album_cat_description
     *
     * @return string 
     */
    public function getAlbumCatDescription()
    {
        return $this->album_cat_description;
    }

    /**
     * Set album_cat_id_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $albumCatIdLang
     * @return albumCategoryLang
     */
    public function setAlbumCatIdLang(\MyCp\mycpBundle\Entity\lang $albumCatIdLang = null)
    {
        $this->album_cat_id_lang = $albumCatIdLang;
    
        return $this;
    }

    /**
     * Get album_cat_id_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getAlbumCatIdLang()
    {
        return $this->album_cat_id_lang;
    }

    /**
     * Set album_cat_id_cat
     *
     * @param \MyCp\mycpBundle\Entity\albumCategory $albumCatIdCat
     * @return albumCategoryLang
     */
    public function setAlbumCatIdCat(\MyCp\mycpBundle\Entity\albumCategory $albumCatIdCat = null)
    {
        $this->album_cat_id_cat = $albumCatIdCat;
    
        return $this;
    }

    /**
     * Get album_cat_id_cat
     *
     * @return \MyCp\mycpBundle\Entity\albumCategory 
     */
    public function getAlbumCatIdCat()
    {
        return $this->album_cat_id_cat;
    }
}