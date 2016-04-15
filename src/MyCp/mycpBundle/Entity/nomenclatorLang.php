<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * faq
 *
 * @ORM\Table(name="nomenclatorlang")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\nomenclatorRepository")
 */
class nomenclatorLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="nom_lang_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $nom_lang_id;
    
     /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="")
     * @ORM\JoinColumn(name="nom_lang_id_lang",referencedColumnName="lang_id")
     */
    private $nom_lang_id_lang;
    
     /**
     * @ORM\ManyToOne(targetEntity="nomenclator",inversedBy="translations")
     * @ORM\JoinColumn(name="nom_lang_id_nomenclator",referencedColumnName="nom_id")
     */
    private $nom_lang_id_nomenclator;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_lang_description", type="string", length=255,nullable=true)
     */
    private $nom_lang_description;
    
    /**
     * Get nom_id
     *
     * @return integer 
     */
    public function getNomLangId()
    {
        return $this->nom_lang_id;
    }

    /**
     * Set nom_lang_description
     *
     * @param string $nomLangDesciption
     * @return nomenclatorLang
     */
    public function setNomLangDescription($nomLangDesciption)
    {
        $this->nom_lang_description = $nomLangDesciption;
    
        return $this;
    }

    /**
     * Get nom_lang_description
     *
     * @return string 
     */
    public function getNomLangDescription()
    {
        return $this->nom_lang_description;
    }

    /**
     * Set nom_lang_id_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $nomLangIdLang
     * @return nomenclatorLang
     */
    public function setNomLangIdLang(\MyCp\mycpBundle\Entity\lang $nomLangIdLang = null)
    {
        $this->nom_lang_id_lang = $nomLangIdLang;
    
        return $this;
    }

    /**
     * Get nom_lang_id_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getNomLangIdLang()
    {
        return $this->nom_lang_id_lang;
    }

    /**
     * Set nom_lang_id_nomenclator
     *
     * @param \MyCp\mycpBundle\Entity\photo $phoLangIdPhoto
     * @return nomenclatorLang
     */
    public function setNomLangIdNomenclator(\MyCp\mycpBundle\Entity\nomenclator $nomLangIdNomenclator = null)
    {
        $this->nom_lang_id_nomenclator = $nomLangIdNomenclator;
    
        return $this;
    }

    /**
     * Get nom_lang_id_nomenclator
     *
     * @return \MyCp\mycpBundle\Entity\photo 
     */
    public function getNomLangIdNomenclator()
    {
        return $this->nom_lang_id_nomenclator;
    }
    
    public function __toString()
    {
        return $this->nom_lang_description;
    }
}