<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * lang
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\langRepository")
 */
class lang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="lang_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $lang_id;

    /**
     * @var string
     *
     * @ORM\Column(name="lang_name", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $lang_name;


    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=5)
     * @Assert\NotBlank()
     * @Assert\Length(max=5)
     */
    private $lang_code;

    /**
     * @var string
     *
     * @ORM\Column(name="lang_active", type="boolean")
     */
    private $lang_active;

    /**
     * @ORM\OneToMany(targetEntity="destinationLang",mappedBy="langs")
     */
    private $destinationsLang;
   
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->destinationsLang = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get lang_id
     *
     * @return integer 
     */
    public function getLangId()
    {
        return $this->lang_id;
    }

    /**
     * Set lang_name
     *
     * @param string $langName
     * @return lang
     */
    public function setLangName($langName)
    {
        $this->lang_name = $langName;
    
        return $this;
    }

    /**
     * Get lang_name
     *
     * @return string 
     */
    public function getLangName()
    {
        return $this->lang_name;
    }

    /**
     * Set lang_code
     *
     * @param string $langCode
     * @return lang
     */
    public function setLangCode($langCode)
    {
        $this->lang_code = $langCode;
    
        return $this;
    }

    /**
     * Get lang_code
     *
     * @return string 
     */
    public function getLangCode()
    {
        return $this->lang_code;
    }

    /**
     * Set lang_active
     *
     * @param boolean $langActive
     * @return lang
     */
    public function setLangActive($langActive)
    {
        $this->lang_active = $langActive;
    
        return $this;
    }

    /**
     * Get lang_active
     *
     * @return boolean 
     */
    public function getLangActive()
    {
        return $this->lang_active;
    }

    /**
     * Add destinationsLang
     *
     * @param \MyCp\mycpBundle\Entity\destinationLang $destinationsLang
     * @return lang
     */
    public function addDestinationsLang(\MyCp\mycpBundle\Entity\destinationLang $destinationsLang)
    {
        $this->destinationsLang[] = $destinationsLang;
    
        return $this;
    }

    /**
     * Remove destinationsLang
     *
     * @param \MyCp\mycpBundle\Entity\destinationLang $destinationsLang
     */
    public function removeDestinationsLang(\MyCp\mycpBundle\Entity\destinationLang $destinationsLang)
    {
        $this->destinationsLang->removeElement($destinationsLang);
    }

    /**
     * Get destinationsLang
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDestinationsLang()
    {
        return $this->destinationsLang;
    }
}