<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipKeywordLang
 *
 * @ORM\Table(name="ownershipkeywordlang")
 * @ORM\Entity
 */
class ownershipKeywordLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="okl_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $okl_id;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="langslang")
     * @ORM\JoinColumn(name="okl_id_lang",referencedColumnName="lang_id")
     */
    private $okl_id_lang;

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="ownershipKeywordLang")
     * @ORM\JoinColumn(name="okl_id_ownership",referencedColumnName="own_id")
     */
    private $okl_ownership;

    /**
     * @var string
     *
     * @ORM\Column(name="okl_keywords", type="string", length=255)
     */
    private $okl_keywords;


    /**
     * Get okl_id
     *
     * @return integer 
     */
    public function getOklId()
    {
        return $this->okl_id;
    }

    /**
     * Set okl_keywords
     *
     * @param string $oklKeywords
     * @return ownershipKeywordLang
     */
    public function setOklKeywords($oklKeywords)
    {
        $this->okl_keywords = $oklKeywords;
    
        return $this;
    }

    /**
     * Get okl_keywords
     *
     * @return string 
     */
    public function getOklKeywords()
    {
        return $this->okl_keywords;
    }

    /**
     * Set okl_id_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $oklIdLang
     * @return ownershipKeywordLang
     */
    public function setOklIdLang(\MyCp\mycpBundle\Entity\lang $oklIdLang = null)
    {
        $this->okl_id_lang = $oklIdLang;
    
        return $this;
    }

    /**
     * Get okl_id_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getOklIdLang()
    {
        return $this->okl_id_lang;
    }

    /**
     * Set okl_ownership
     *
     * @param \MyCp\mycpBundle\Entity\ownership $oklOwnership
     * @return ownershipKeywordLang
     */
    public function setOklOwnership(\MyCp\mycpBundle\Entity\ownership $oklOwnership = null)
    {
        $this->okl_ownership = $oklOwnership;
    
        return $this;
    }

    /**
     * Get okl_ownership
     *
     * @return \MyCp\mycpBundle\Entity\ownership 
     */
    public function getOklOwnership()
    {
        return $this->okl_ownership;
    }
}