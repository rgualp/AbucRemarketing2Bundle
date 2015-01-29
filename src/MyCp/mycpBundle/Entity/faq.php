<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * faq
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\faqRepository")
 */
class faq
{
    /**
     * @var integer
     *
     * @ORM\Column(name="faq_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $faq_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="faq_order", type="integer")
     */
    private $faq_order;

    /**
     * @var boolean
     *
     * @ORM\Column(name="faq_active", type="boolean")
     */
    private $faq_active;

    /**
     * @ORM\ManyToOne(targetEntity="faqCategory",inversedBy="")
     * @ORM\JoinColumn(name="faq_faq_cat_id",referencedColumnName="faq_cat_id")
     */
    private $faq_category;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->destinationsLang = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get faq_id
     *
     * @return integer
     */
    public function getFaqId()
    {
        return $this->faq_id;
    }

    /**
     * Set faq_order
     *
     * @param integer $faqOrder
     * @return faq
     */
    public function setFaqOrder($faqOrder)
    {
        $this->faq_order = $faqOrder;

        return $this;
    }

    /**
     * Get faq_order
     *
     * @return integer
     */
    public function getFaqOrder()
    {
        return $this->faq_order;
    }

    /**
     * Set faq_active
     *
     * @param boolean $faqActive
     * @return faq
     */
    public function setFaqActive($faqActive)
    {
        $this->faq_active = $faqActive;

        return $this;
    }

    /**
     * Get faq_active
     *
     * @return boolean
     */
    public function getFaqActive()
    {
        return $this->faq_active;
    }

    /**
     * Set faq_category
     *
     * @param \MyCp\mycpBundle\Entity\faqCategory $faqCategory
     * @return faq
     */
    public function setFaqCategory(\MyCp\mycpBundle\Entity\faqCategory $faqCategory = null)
    {
        $this->faq_category = $faqCategory;

        return $this;
    }

    /**
     * Get faq_category
     *
     * @return \MyCp\mycpBundle\Entity\faqCategory
     */
    public function getFaqCategory()
    {
        return $this->faq_category;
    }
}