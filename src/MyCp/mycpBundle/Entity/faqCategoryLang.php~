<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * faqCategoryLang
 *
 * @ORM\Table(name="faqcategorylang")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\faqCategoryLangRepository")
 */
class faqCategoryLang
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
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="")
     * @ORM\JoinColumn(name="faq_cat_id_lang",referencedColumnName="lang_id")
     */
    private $faq_cat_id_lang;

    /**
     * @var string
     *
     * @ORM\Column(name="faq_cat_description", type="string", length=255)
     */
    private $faq_cat_description;

    /**
     * @ORM\ManyToOne(targetEntity="faqCategory",inversedBy="")
     * @ORM\JoinColumn(name="faq_cat_id_cat",referencedColumnName="faq_cat_id")
     */
    private $faq_cat_id_cat;



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
     * Set faq_cat_description
     *
     * @param string $faqCatDescription
     * @return faqCategoryLang
     */
    public function setFaqCatDescription($faqCatDescription)
    {
        $this->faq_cat_description = $faqCatDescription;
    
        return $this;
    }

    /**
     * Get faq_cat_description
     *
     * @return string 
     */
    public function getFaqCatDescription()
    {
        return $this->faq_cat_description;
    }

    /**
     * Set faq_cat_id_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $faqCatIdLang
     * @return faqCategoryLang
     */
    public function setFaqCatIdLang(\MyCp\mycpBundle\Entity\lang $faqCatIdLang = null)
    {
        $this->faq_cat_id_lang = $faqCatIdLang;
    
        return $this;
    }

    /**
     * Get faq_cat_id_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getFaqCatIdLang()
    {
        return $this->faq_cat_id_lang;
    }

    /**
     * Set faq_cat_id_cat
     *
     * @param \MyCp\mycpBundle\Entity\faqCategory $faqCatIdCat
     * @return faqCategoryLang
     */
    public function setFaqCatIdCat(\MyCp\mycpBundle\Entity\faqCategory $faqCatIdCat = null)
    {
        $this->faq_cat_id_cat = $faqCatIdCat;
    
        return $this;
    }

    /**
     * Get faq_cat_id_cat
     *
     * @return \MyCp\mycpBundle\Entity\faqCategory 
     */
    public function getFaqCatIdCat()
    {
        return $this->faq_cat_id_cat;
    }
}