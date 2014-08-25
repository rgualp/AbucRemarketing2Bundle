<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * metaLang
 *
 * @ORM\Table(name="metatag")
 * @ORM\Entity
 */
class metaTag
{
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
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", length=255)
     */
    private $meta_title;

    /**
     * @ORM\OneToMany(targetEntity="metaLang",mappedBy="meta_tag")
     */
    private $meta_langs;

    public function __construct()
    {
        $this->meta_langs = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getMetaLangs()
    {
        return $this->meta_langs;
    }
}
