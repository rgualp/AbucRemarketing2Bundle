<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * information
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\informationRepository")
 *
 */
class information
{
    /**
     * @var integer
     *
     * @ORM\Column(name="info_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $info_id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="info_fixed", type="boolean")
     */
    private $info_fixed;
    
    /**
     * @ORM\ManyToOne(targetEntity="nomenclator",inversedBy="")
     * @ORM\JoinColumn(name="info_id_nom",referencedColumnName="nom_id", nullable=true)
     */
    private $info_id_nom;

    /**
     * @ORM\OneToMany(targetEntity="informationLang",mappedBy="info_lang_info")
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
     * Get info_id
     *
     * @return integer 
     */
    public function getInfoId()
    {
        return $this->info_id;
    }
    
    /**
     * Set info_id_nom
     *
     * @param \MyCp\mycpBundle\Entity\nomenclator $infoIdNom
     * @return information
     */
    public function setInfoIdNom(\MyCp\mycpBundle\Entity\nomenclator $infoIdNom = null)
    {
        $this->info_id_nom = $infoIdNom;
    
        return $this;
    }

    /**
     * Get info_id_nom
     *
     * @return \MyCp\mycpBundle\Entity\nomenclator 
     */
    public function getInfoIdNom()
    {
        return $this->info_id_nom;
    }

    /**
     * Set info_fixed
     *
     * @param boolean $infoFixed
     * @return information
     */
    public function setInfoFixed($infoFixed)
    {
        $this->info_fixed = $infoFixed;
    
        return $this;
    }

    /**
     * Get info_fixed
     *
     * @return boolean 
     */
    public function getInfoFixed()
    {
        return $this->info_fixed;
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
        return (count($this->translations) > 0) ? "Información general ".$this->translations[0]->getInfoLangContent(). " y sus traducciones." : "Información general con id ".$this->getInfoId()." y sus traducciones.";
    }
    
}