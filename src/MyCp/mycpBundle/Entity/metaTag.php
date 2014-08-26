<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * metaLang
 *
 * @ORM\Table(name="metatag")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\metaTagRepository")
 */
class metaTag
{
    /**
     * All allowed sections
     */
    const SECTION_GENERAL = 1;
    const SECTION_HOMEPAGE = 2;
    const SECTION_DESTINATIONS = 3;
    const SECTION_ACCOMMODATIONS = 4;
    const SECTION_MYCASATRIP = 5;
    const SECTION_FAVORITES = 6;
    const SECTION_HOW_IT_WORKS = 7;
    const SECTION_ACCOMMODATIONS_LISTS = 8;
    const SECTION_LAST_ADDED = 9;
    const SECTION_ECONOMICS_LIST = 10;
    const SECTION_MIDRANGE_LIST = 11;
    const SECTION_PREMIUM_LIST = 12;
    const SECTION_MOST_VISITED_CITIES = 13;
    const SECTION_WHO_WE_ARE = 14;
    const SECTION_CONTACT_US = 15;
    const SECTION_FAQS = 16;
    const SECTION_LEGAL_TERM = 17;
    const SECTION_SECURITY_AND_PRIVACY = 18;
    const SECTION_SITEMAP = 19;


    /**
     * Contains all possible sections
     *
     * @var array
     */
    private $sections = array(
        self::SECTION_GENERAL,
        self::SECTION_HOMEPAGE,
        self::SECTION_DESTINATIONS,
        self::SECTION_ACCOMMODATIONS,
        self::SECTION_MYCASATRIP,
        self::SECTION_FAVORITES,
        self::SECTION_HOW_IT_WORKS,
        self::SECTION_ACCOMMODATIONS_LISTS,
        self::SECTION_LAST_ADDED,
        self::SECTION_ECONOMICS_LIST,
        self::SECTION_MIDRANGE_LIST,
        self::SECTION_PREMIUM_LIST,
        self::SECTION_MOST_VISITED_CITIES,
        self::SECTION_WHO_WE_ARE,
        self::SECTION_CONTACT_US,
        self::SECTION_FAQS,
        self::SECTION_LEGAL_TERM,
        self::SECTION_SECURITY_AND_PRIVACY,
        self::SECTION_SITEMAP,

    );

    /**
     * @var integer
     *
     * @ORM\Column(name="meta_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $meta_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="meta_section", type="integer")
     */
    private $meta_section;


     /**
     * @ORM\ManyToOne(targetEntity="metaTag",inversedBy="meta_children")
     * @ORM\JoinColumn(name="meta_parent",referencedColumnName="meta_id", nullable=true)
     */
    private $meta_parent;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", length=255)
     */
    private $meta_title;

    /**
     * @ORM\OneToMany(targetEntity="metaLang",mappedBy="meta_tag")
     */
    private $meta_langs;

    /**
     * @ORM\OneToMany(targetEntity="metaTag",mappedBy="meta_section_parent")
     */
    private $meta_children;

    public function __construct()
    {
        $this->meta_langs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->meta_children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get meta_id
     *
     * @return integer
     */
    public function getMetaId()
    {
        return $this->meta_id;
    }

    /**
     * Set meta_section
     *
     * @param integer $metaSection
     * @return metaTag
     */
    public function setMetaSection($metaSection)
    {
        if (!in_array($metaSection, $this->sections)) {
            throw new \InvalidArgumentException("Section $metaSection not allowed");
        }

        $this->meta_section = $metaSection;

        return $this;
    }

    /**
     * Get meta_section
     *
     * @return integer
     */
    public function getMetaSection()
    {
        return $this->meta_section;
    }

    /**
     * Set meta_title
     *
     * @param string $metaTitle
     * @return metaTag
     */
    public function setMetaTitle($metaTitle)
    {
        $this->meta_title = $metaTitle;

        return $this;
    }

    /**
     * Get meta_title
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->meta_title;
    }

    /**
     * Set meta_parent
     *
     * @param metaTag $metaParent
     * @return metaTag
     */
    public function setMetaParent($metaParent)
    {
        $this->meta_parent = $metaParent;

        return $this;
    }

    /**
     * Get meta_parent
     *
     * @return metaTag
     */
    public function getMetaParent()
    {
        return $this->meta_parent;
    }

    public function getMetaLangs()
    {
        return $this->meta_langs;
    }

    public function getMetaChildren()
    {
        return $this->meta_children;
    }
}
