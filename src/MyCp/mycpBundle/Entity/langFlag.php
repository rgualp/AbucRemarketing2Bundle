<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * langflag
 *
 * @ORM\Table(name="langflag")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\langPhotoRepository")
 */
class langFlag
{
    /**
     * @var integer
     *
     * @ORM\Column(name="lang_flag_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $lang_flag_id;

    /**
     * @ORM\ManyToOne(targetEntity="photo",inversedBy="")
     * @ORM\JoinColumn(name="lang_flag_photo",referencedColumnName="pho_id")
     */
    private $lang_flag_photo;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="")
     * @ORM\JoinColumn(name="lang_flag_lang_id",referencedColumnName="lang_id")
     */
    private $lang_flag_lang_id;


    /**
     * Get lang_flag_id
     *
     * @return integer 
     */
    public function getLangFlagId()
    {
        return $this->lang_flag_id;
    }

    /**
     * Set lang_flag_photo
     *
     * @param \MyCp\mycpBundle\Entity\photo $langFlagPhoto
     * @return langFlag
     */
    public function setLangFlagPhoto(\MyCp\mycpBundle\Entity\photo $langFlagPhoto = null)
    {
        $this->lang_flag_photo = $langFlagPhoto;
    
        return $this;
    }

    /**
     * Get lang_flag_photo
     *
     * @return \MyCp\mycpBundle\Entity\photo 
     */
    public function getLangFlagPhoto()
    {
        return $this->lang_flag_photo;
    }

    /**
     * Set lang_flag_lang_id
     *
     * @param \MyCp\mycpBundle\Entity\lang $langFlagLangId
     * @return langFlag
     */
    public function setLangFlagLangId(\MyCp\mycpBundle\Entity\lang $langFlagLangId = null)
    {
        $this->lang_flag_lang_id = $langFlagLangId;
    
        return $this;
    }

    /**
     * Get lang_flag_lang_id
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getLangFlagLangId()
    {
        return $this->lang_flag_lang_id;
    }
}