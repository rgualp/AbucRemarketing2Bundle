<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipDescriptionLang
 *
 * @ORM\Table(name="ownershipdescriptionlang")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\ownershipDescriptionLangRepository")
 */
class ownershipDescriptionLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="odl_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $odl_id;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="odl_langs" ,cascade={"persist"})
     * @ORM\JoinColumn(name="odl_id_lang",referencedColumnName="lang_id")
     */
    private $odl_id_lang;

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="own_description_langs")
     * @ORM\JoinColumn(name="odl_id_ownership",referencedColumnName="own_id")
     */
    private $odl_ownership;

    /**
     * @var string
     *
     * @ORM\Column(name="odl_description", type="text")
     */
    private $odl_description;

    /**
     * @var string
     *
     * @ORM\Column(name="odl_brief_description", type="text")
     */
    private $odl_brief_description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="odl_automatic_translation", type="boolean")
     */
    private $odl_automatic_translation;

    /**
     * Get odl_id
     *
     * @return integer 
     */
    public function getOdlId()
    {
        return $this->odl_id;
    }

    /**
     * Set odl_description
     *
     * @param string $odlDescription
     * @return ownershipDescriptionLang
     */
    public function setOdlDescription($odlDescription)
    {
        $this->odl_description = $odlDescription;
    
        return $this;
    }

    /**
     * Get odl_description
     *
     * @return string 
     */
    public function getOdlDescription()
    {
        return $this->odl_description;
    }

    /**
     * Set odl_id_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $odlIdLang
     * @return ownershipDescriptionLang
     */
    public function setOdlIdLang(\MyCp\mycpBundle\Entity\lang $odlIdLang = null)
    {
        $this->odl_id_lang = $odlIdLang;
    
        return $this;
    }

    /**
     * Get odl_id_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getOdlIdLang()
    {
        return $this->odl_id_lang;
    }

    /**
     * Set odl_ownership
     *
     * @param \MyCp\mycpBundle\Entity\ownership $odlOwnership
     * @return ownershipDescriptionLang
     */
    public function setOdlOwnership(\MyCp\mycpBundle\Entity\ownership $odlOwnership = null)
    {
        $this->odl_ownership = $odlOwnership;
    
        return $this;
    }

    /**
     * Get odl_ownership
     *
     * @return \MyCp\mycpBundle\Entity\ownership 
     */
    public function getOdlOwnership()
    {
        return $this->odl_ownership;
    }

    /**
     * Set odl_brief_description
     *
     * @param string $odlBriefDescription
     * @return ownershipDescriptionLang
     */
    public function setOdlBriefDescription($odlBriefDescription)
    {
        $this->odl_brief_description = $odlBriefDescription;
    
        return $this;
    }

    /**
     * Get odl_brief_description
     *
     * @return string 
     */
    public function getOdlBriefDescription()
    {
        return $this->odl_brief_description;
    }

    /**
     * Set odl_automatic_translation
     *
     * @param boolean $odlAutomaticTranslation
     * @return ownershipDescriptionLang
     */
    public function setOdlAutomaticTranslation($odlAutomaticTranslation)
    {
        $this->odl_automatic_translation = $odlAutomaticTranslation;

        return $this;
    }

    /**
     * Get odl_automatic_translation
     *
     * @return boolean
     */
    public function getOdlAutomaticTranslation()
    {
        return $this->odl_automatic_translation;
    }

    /**
     * Retur object as string
     * @return string
     */
    public function __toString()
    {
        return $this->getOdlDescription();
    }
}