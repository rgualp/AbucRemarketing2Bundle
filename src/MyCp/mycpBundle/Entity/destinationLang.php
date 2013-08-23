<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * destinationLang
 *
 * @ORM\Table(name="destinationlang")
 * @ORM\Entity
 */
class destinationLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="des_lang_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $des_lang_id;

    /**
     * @var string
     *
     * @ORM\Column(name="des_lang_brief", type="text")
     */
    private $des_lang_brief;

    /**
     * @var string
     *
     * @ORM\Column(name="des_lang_desc", type="text")
     */
    private $des_lang_desc;

    /**
     * @ORM\ManyToOne(targetEntity="destination",inversedBy="destinationsLang")
     * @ORM\JoinColumn(name="des_lang_des_id",referencedColumnName="des_id")
     */
    private $des_lang_destination;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="destinationsLang")
     * @ORM\JoinColumn(name="des_lang_lang_id",referencedColumnName="lang_id")
     */
    private $des_lang_lang;


    /**
     * Get des_lang_id
     *
     * @return integer 
     */
    public function getDesLangId()
    {
        return $this->des_lang_id;
    }

    /**
     * Set des_lang_brief
     *
     * @param string $desLangBrief
     * @return destinationLang
     */
    public function setDesLangBrief($desLangBrief)
    {
        $this->des_lang_brief = $desLangBrief;
    
        return $this;
    }

    /**
     * Get des_lang_brief
     *
     * @return string 
     */
    public function getDesLangBrief()
    {
        return $this->des_lang_brief;
    }

    /**
     * Set des_lang_desc
     *
     * @param string $desLangDesc
     * @return destinationLang
     */
    public function setDesLangDesc($desLangDesc)
    {
        $this->des_lang_desc = $desLangDesc;
    
        return $this;
    }

    /**
     * Get des_lang_desc
     *
     * @return string 
     */
    public function getDesLangDesc()
    {
        return $this->des_lang_desc;
    }

    /**
     * Set des_lang_destination
     *
     * @param \MyCp\mycpBundle\Entity\destination $desLangDestination
     * @return destinationLang
     */
    public function setDesLangDestination(\MyCp\mycpBundle\Entity\destination $desLangDestination = null)
    {
        $this->des_lang_destination = $desLangDestination;
    
        return $this;
    }

    /**
     * Get des_lang_destination
     *
     * @return \MyCp\mycpBundle\Entity\destination 
     */
    public function getDesLangDestination()
    {
        return $this->des_lang_destination;
    }

    /**
     * Set des_lang_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $desLangLang
     * @return destinationLang
     */
    public function setDesLangLang(\MyCp\mycpBundle\Entity\lang $desLangLang = null)
    {
        $this->des_lang_lang = $desLangLang;
    
        return $this;
    }

    /**
     * Get des_lang_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getDesLangLang()
    {
        return $this->des_lang_lang;
    }
}