<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * informationlang
 *
 * @ORM\Table(name="informationlang")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\informationLangRepository")
 */
class informationLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="info_lang_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $info_lang_id;

    /**
     * @var string
     *
     * @ORM\Column(name="info_lang_name", type="text")
     */
    private $info_lang_name;

    /**
     * @var string
     *
     * @ORM\Column(name="info_lang_content", type="text")
     */
    private $info_lang_content;

    /**
     * @ORM\ManyToOne(targetEntity="information",inversedBy="translations")
     * @ORM\JoinColumn(name="info_lang_info",referencedColumnName="info_id")
     */
    private $info_lang_info;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="")
     * @ORM\JoinColumn(name="info_lang_lang",referencedColumnName="lang_id")
     */
    private $info_lang_lang;


    /**
     * Get info_lang_id
     *
     * @return integer 
     */
    public function getInfoLangId()
    {
        return $this->info_lang_id;
    }

    /**
     * Set info_lang_name
     *
     * @param string $infoLangName
     * @return informationLang
     */
    public function setInfoLangName($infoLangName)
    {
        $this->info_lang_name = $infoLangName;
    
        return $this;
    }

    /**
     * Get info_lang_name
     *
     * @return string 
     */
    public function getInfoLangName()
    {
        return $this->info_lang_name;
    }

    /**
     * Set info_lang_content
     *
     * @param string $infoLangContent
     * @return informationLang
     */
    public function setInfoLangContent($infoLangContent)
    {
        $this->info_lang_content = $infoLangContent;
    
        return $this;
    }

    /**
     * Get info_lang_content
     *
     * @return string 
     */
    public function getInfoLangContent()
    {
        return $this->info_lang_content;
    }

    /**
     * Set info_lang_info
     *
     * @param \MyCp\mycpBundle\Entity\information $infoLangInfo
     * @return informationLang
     */
    public function setInfoLangInfo(\MyCp\mycpBundle\Entity\information $infoLangInfo = null)
    {
        $this->info_lang_info = $infoLangInfo;
    
        return $this;
    }

    /**
     * Get info_lang_info
     *
     * @return \MyCp\mycpBundle\Entity\information 
     */
    public function getInfoLangInfo()
    {
        return $this->info_lang_info;
    }

    /**
     * Set info_lang_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $infoLangLang
     * @return informationLang
     */
    public function setInfoLangLang(\MyCp\mycpBundle\Entity\lang $infoLangLang = null)
    {
        $this->info_lang_lang = $infoLangLang;
    
        return $this;
    }

    /**
     * Get info_lang_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getInfoLangLang()
    {
        return $this->info_lang_lang;
    }
}