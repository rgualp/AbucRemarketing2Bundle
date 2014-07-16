<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * metaLang
 *
 * @ORM\Table(name="metalang")
 * @ORM\Entity
 */
class metaLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="meta_lang_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $meta_lang_id;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_lang_description", type="string", length=255)
     */
    private $meta_lang_description;


    /**
     * @var string
     *
     * @ORM\Column(name="meta_lang_keywords", type="string", length=255)
     */
    private $meta_lang_keywords;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="lang_metas")
     * @ORM\JoinColumn(name="meta_lang_lang_id",referencedColumnName="lang_id")
     */
    private $meta_lang_lang;

    /**
     * Get meta_lang_id
     *
     * @return integer 
     */
    public function getMetaLangId()
    {
        return $this->meta_lang_id;
    }

    /**
     * Set meta_lang_description
     *
     * @param string $metaLangDescription
     * @return metaLang
     */
    public function setMetaLangDescription($metaLangDescription)
    {
        $this->meta_lang_description = $metaLangDescription;

        return $this;
    }

    /**
     * Get meta_lang_description
     *
     * @return string 
     */
    public function getMetaLangDescription()
    {
        return $this->meta_lang_description;
    }

    /**
     * Set meta_lang_keywords
     *
     * @param string $metaLangKeywords
     * @return metaLang
     */
    public function setMetaLangKeywords($metaLangKeywords)
    {
        $this->meta_lang_keywords = $metaLangKeywords;

        return $this;
    }

    /**
     * Get meta_lang_keywords
     *
     * @return string 
     */
    public function getMetaLangKeywords()
    {
        return $this->meta_lang_keywords;
    }

    /**
     * Set meta_lang_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $metaLangLang
     * @return metaLang
     */
    public function setMetaLangLang(\MyCp\mycpBundle\Entity\lang $metaLangLang = null)
    {
        $this->meta_lang_lang = $metaLangLang;

        return $this;
    }

    /**
     * Get meta_lang_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getMetaLangLang()
    {
        return $this->meta_lang_lang;
    }
}
