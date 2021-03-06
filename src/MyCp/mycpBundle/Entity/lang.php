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
     * @ORM\OneToMany(targetEntity="destinationLang",mappedBy="des_lang_lang")
     */
    private $destinationsLang;

    /**
     * @ORM\OneToMany(targetEntity="ownershipDescriptionLang",mappedBy="odl_id_lang")
     */
    private $odl_langs;

    /**
     * @ORM\OneToMany(targetEntity="metaLang",mappedBy="meta_lang_lang")
     */
    private $lang_metas;

    /**
     * @ORM\OneToMany(targetEntity="destinationCategoryLang",mappedBy="des_cat_id_lang")
     */
    private $lang_destination_categories;

    /**
     * @ORM\OneToMany(targetEntity="ownershipKeywordLang",mappedBy="okl_id_lang")
     */
    private $lang_ownership_keywords;

    /**
     * @ORM\OneToMany(targetEntity="destinationKeywordLang",mappedBy="dkl_id_lang")
     */
    private $lang_destination_keywords;

    /**
     * @ORM\OneToMany(targetEntity="newsletterEmail",mappedBy="language")
     */
    private $newsletterEmails;

    /**
     * @ORM\OneToMany(targetEntity="newsletterContent",mappedBy="language")
     */
    private $newsletterContents;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->destinationsLang = new \Doctrine\Common\Collections\ArrayCollection();
        $this->odl_langs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lang_metas = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lang_destination_categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lang_ownership_keywords = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lang_destination_keywords = new \Doctrine\Common\Collections\ArrayCollection();
        $this->newsletterEmails = new \Doctrine\Common\Collections\ArrayCollection();
        $this->newsletterContents = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getOwnershipDescriptionLangs()
    {
        return $this->odl_langs;
    }

    public function getLangMetas()
    {
        return $this->lang_metas;
    }

    public function getLangDestinationCategories()
    {
        return $this->lang_destination_categories;
    }

    public function getLangOwnershipKeywords()
    {
        return $this->lang_ownership_keywords;
    }

    public function getLangDestinationKeywords()
    {
        return $this->lang_destination_keywords;
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

    /*Logs functions*/
    public function getLogDescription()
    {
        return "Lenguaje ".$this->getLangCode();
    }

    /**
     * @return mixed
     */
    public function getNewsletterEmails()
    {
        return $this->newsletterEmails;
    }

    /**
     * @param mixed $newsletterEmails
     * @return mixed
     */
    public function setNewsletterEmails($newsletterEmails)
    {
        $this->newsletterEmails = $newsletterEmails;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOdlLangs()
    {
        return $this->odl_langs;
    }

    /**
     * @param mixed $odl_langs
     * @return mixed
     */
    public function setOdlLangs($odl_langs)
    {
        $this->odl_langs = $odl_langs;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNewsletterContents()
    {
        return $this->newsletterContents;
    }

    /**
     * @param mixed $newsletterContents
     * @return mixed
     */
    public function setNewsletterContents($newsletterContents)
    {
        $this->newsletterContents = $newsletterContents;
        return $this;
    }


}