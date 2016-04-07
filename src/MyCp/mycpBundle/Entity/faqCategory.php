<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * faqCategory
 *
 * @ORM\Table(name="faqcategory")
 * @ORM\Entity
 */
class faqCategory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="faq_cat_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $faq_cat_id;

    /**
     * @ORM\OneToMany(targetEntity="faqCategoryLang",mappedBy="faq_cat_id_cat")
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
     * Get faq_cat_id
     *
     * @return integer 
     */
    public function getFaqCatId()
    {
        return $this->faq_cat_id;
    }

    /**
     * @return mixed
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param mixed $translations
     * @return mixed
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
        return $this;
    }

    /*Logs functions*/
    public function getLogDescription()
    {
        return (count($this->translations) > 0) ? "Categoría de FAQ ".$this->translations[0]->getFaqCatDescription(). " y sus traducciones." : "Categoría de FAQ con id ".$this->getFaqCatId()." y sus traducciones.";
    }

}