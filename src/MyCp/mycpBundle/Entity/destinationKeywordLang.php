<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * destinationKeywordLang
 *
 * @ORM\Table(name="destinationkeywordlang")
 * @ORM\Entity
 */
class destinationKeywordLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="dkl_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $dkl_id;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="lang_destination_keywords")
     * @ORM\JoinColumn(name="dkl_id_lang",referencedColumnName="lang_id")
     */
    private $dkl_id_lang;

    /**
     * @ORM\ManyToOne(targetEntity="destination",inversedBy="des_keyword_langs")
     * @ORM\JoinColumn(name="dkl_id_destination",referencedColumnName="des_id")
     */
    private $dkl_destination;

    /**
     * @var string
     *
     * @ORM\Column(name="dkl_keywords", type="string", length=255)
     */
    private $dkl_keywords;

    /**
     * @var string
     *
     * @ORM\Column(name="dkl_description", type="string", length=255)
     */
    private $dkl_description;


    /**
     * Get dkl_id
     *
     * @return integer
     */
    public function getDklId()
    {
        return $this->dkl_id;
    }

    /**
     * Set dkl_keywords
     *
     * @param string $dklKeywords
     * @return destinationKeywordLang
     */
    public function setDklKeywords($dklKeywords)
    {
        $this->dkl_keywords = $dklKeywords;

        return $this;
    }

    /**
     * Get dkl_keywords
     *
     * @return string
     */
    public function getDklKeywords()
    {
        return $this->dkl_keywords;
    }

    /**
     * Set dkl_description
     *
     * @param string $dklDescription
     * @return destinationKeywordLang
     */
    public function setDklDescription($dklDescription)
    {
        $this->dkl_description = $dklDescription;

        return $this;
    }

    /**
     * Get dkl_description
     *
     * @return string
     */
    public function getDklDescription()
    {
        return $this->dkl_description;
    }

    /**
     * Set dkl_id_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $dklIdLang
     * @return destinationKeywordLang
     */
    public function setDklIdLang(\MyCp\mycpBundle\Entity\lang $dklIdLang = null)
    {
        $this->dkl_id_lang = $dklIdLang;

        return $this;
    }

    /**
     * Get dkl_id_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang
     */
    public function getDklIdLang()
    {
        return $this->dkl_id_lang;
    }

    /**
     * Set dkl_destination
     *
     * @param \MyCp\mycpBundle\Entity\destination $dklDestination
     * @return destinationKeywordLang
     */
    public function setDklDestination(\MyCp\mycpBundle\Entity\destination $dklDestination = null)
    {
        $this->dkl_destination = $dklDestination;

        return $this;
    }

    /**
     * Get dkl_destination
     *
     * @return \MyCp\mycpBundle\Entity\destination
     */
    public function getDklDestination()
    {
        return $this->dkl_destination;
    }
}
