<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * faq
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\nomenclatorRepository")
 */
class nomenclator
{
    /**
     * @var integer
     *
     * @ORM\Column(name="nom_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $nom_id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_name", type="string", length=255)
     */
    private $nom_name;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_category", type="string", length=255)
     */
    private $nom_category;

    /**
     * @ORM\OneToMany(targetEntity="nomenclatorLang",mappedBy="nom_lang_id_nomenclator")
     */
    private $translations;

    /**
     * @ORM\OneToMany(targetEntity="ownershipPayment",mappedBy="method")
     */
    private $payments;

    /**
     * @ORM\OneToMany(targetEntity="failure",mappedBy="type")
     */
    private $failureTypes;

    /**
     * @ORM\OneToMany(targetEntity="ownershipRankingExtra",mappedBy="category")
     */
    private $rankingCategories;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->failureTypes = new ArrayCollection();
        $this->rankingCategories = new ArrayCollection();
    }
    
    /**
     * Get nom_id
     *
     * @return integer 
     */
    public function getNomId()
    {
        return $this->nom_id;
    }

    /**
     * Set nom_name
     *
     * @param string $nomName
     * @return nomenclator
     */
    public function setNomName($nomName)
    {
        $this->nom_name = $nomName;
    
        return $this;
    }

    /**
     * Get nom_name
     *
     * @return string 
     */
    public function getNomName()
    {
        return $this->nom_name;
    }

    /**
     * Set nom_category
     *
     * @param string $nomCategory
     * @return nomenclator
     */
    public function setNomCategory($nomCategory)
    {
        $this->nom_category = $nomCategory;
    
        return $this;
    }

    /**
     * Get nom_category
     *
     * @return string 
     */
    public function getNomCategory()
    {
        return $this->nom_category;
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

    /**
     * @return mixed
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param mixed $payments
     * @return mixed
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFailureTypes()
    {
        return $this->failureTypes;
    }

    /**
     * @param mixed $failureTypes
     * @return mixed
     */
    public function setFailureTypes($failureTypes)
    {
        $this->failureTypes = $failureTypes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRankingCategories()
    {
        return $this->rankingCategories;
    }

    /**
     * @param mixed $rankingCategories
     * @return mixed
     */
    public function setRankingCategories($rankingCategories)
    {
        $this->rankingCategories = $rankingCategories;
        return $this;
    }



}