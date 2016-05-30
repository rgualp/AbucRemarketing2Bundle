<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * photolang
 *
 * @ORM\Table(name="photolang")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\photoLangRepository")
 */
class photoLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pho_lang_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $pho_lang_id;

    /**
     * @var string
     *
     * @ORM\Column(name="pho_lang_description", type="string", length=255,nullable=true)
     */
    private $pho_lang_description;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="")
     * @ORM\JoinColumn(name="pho_lang_id_lang",referencedColumnName="lang_id")
     */
    private $pho_lang_id_lang;

    /**
     * @ORM\ManyToOne(targetEntity="photo",inversedBy="photo_langs")
     * @ORM\JoinColumn(name="pho_lang_id_photo",referencedColumnName="pho_id")
     */
    private $pho_lang_id_photo;

    /**
     * Get pho_lang_id
     *
     * @return integer 
     */
    public function getPhoLangId()
    {
        return $this->pho_lang_id;
    }

    /**
     * Set pho_lang_description
     *
     * @param string $phoLangDescription
     * @return photoLang
     */
    public function setPhoLangDescription($phoLangDescription)
    {
        $this->pho_lang_description = $phoLangDescription;
    
        return $this;
    }

    /**
     * Get pho_lang_description
     *
     * @return string 
     */
    public function getPhoLangDescription()
    {
        return $this->pho_lang_description;
    }

    /**
     * Set pho_lang_id_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $phoLangIdLang
     * @return photoLang
     */
    public function setPhoLangIdLang(\MyCp\mycpBundle\Entity\lang $phoLangIdLang = null)
    {
        $this->pho_lang_id_lang = $phoLangIdLang;
    
        return $this;
    }

    /**
     * Get pho_lang_id_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getPhoLangIdLang()
    {
        return $this->pho_lang_id_lang;
    }

    /**
     * Set pho_lang_id_photo
     *
     * @param \MyCp\mycpBundle\Entity\photo $phoLangIdPhoto
     * @return photoLang
     */
    public function setPhoLangIdPhoto(\MyCp\mycpBundle\Entity\photo $phoLangIdPhoto = null)
    {
        $this->pho_lang_id_photo = $phoLangIdPhoto;
    
        return $this;
    }

    /**
     * Get pho_lang_id_photo
     *
     * @return \MyCp\mycpBundle\Entity\photo 
     */
    public function getPhoLangIdPhoto()
    {
        return $this->pho_lang_id_photo;
    }
}