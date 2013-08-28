<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * albumLang
 *
 * @ORM\Table(name="albumlang")
 * @ORM\Entity
 */
class albumLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="album_lang_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $album_lang_id;

    /**
     * @var string
     *
     * @ORM\Column(name="album_lang_name", type="string", length=255)
     */
    private $album_lang_name;

    /**
     * @var string
     *
     * @ORM\Column(name="album_lang_brief_description", type="string", length=255)
     */
    private $album_lang_brief_description;


    /**
     * @ORM\ManyToOne(targetEntity="album",inversedBy="albumsLang")
     * @ORM\JoinColumn(name="album_lang_album_id",referencedColumnName="album_id")
     */
    private $album_lang_album;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="albumsLang")
     * @ORM\JoinColumn(name="album_lang_lang_id",referencedColumnName="lang_id")
     */
    private $album_lang_lang;

    /**
     * Get album_lang_id
     *
     * @return integer 
     */
    public function getAlbumLangId()
    {
        return $this->album_lang_id;
    }

    /**
     * Set album_lang_name
     *
     * @param string $albumLangName
     * @return albumLang
     */
    public function setAlbumLangName($albumLangName)
    {
        $this->album_lang_name = $albumLangName;
    
        return $this;
    }

    /**
     * Get album_lang_name
     *
     * @return string 
     */
    public function getAlbumLangName()
    {
        return $this->album_lang_name;
    }

    /**
     * Set album_lang_brief_description
     *
     * @param string $albumLangBriefDescription
     * @return albumLang
     */
    public function setAlbumLangBriefDescription($albumLangBriefDescription)
    {
        $this->album_lang_brief_description = $albumLangBriefDescription;
    
        return $this;
    }

    /**
     * Get album_lang_brief_description
     *
     * @return string 
     */
    public function getAlbumLangBriefDescription()
    {
        return $this->album_lang_brief_description;
    }

    /**
     * Set album_lang_album
     *
     * @param \MyCp\mycpBundle\Entity\album $albumLangAlbum
     * @return albumLang
     */
    public function setAlbumLangAlbum(\MyCp\mycpBundle\Entity\album $albumLangAlbum = null)
    {
        $this->album_lang_album = $albumLangAlbum;
    
        return $this;
    }

    /**
     * Get album_lang_album
     *
     * @return \MyCp\mycpBundle\Entity\album 
     */
    public function getAlbumLangAlbum()
    {
        return $this->album_lang_album;
    }

    /**
     * Set album_lang_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $albumLangLang
     * @return albumLang
     */
    public function setAlbumLangLang(\MyCp\mycpBundle\Entity\lang $albumLangLang = null)
    {
        $this->album_lang_lang = $albumLangLang;
    
        return $this;
    }

    /**
     * Get album_lang_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getAlbumLangLang()
    {
        return $this->album_lang_lang;
    }
}