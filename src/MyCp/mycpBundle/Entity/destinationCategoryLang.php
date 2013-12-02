<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * destinationCategoryLang
 *
 * @ORM\Table(name="destinationcategorylang")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\destinationCategoryLangRepository")
 */
class destinationCategoryLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="des_cat_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $des_cat_id;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="desCatLang")
     * @ORM\JoinColumn(name="des_cat_id_lang",referencedColumnName="lang_id")
     */
    private $des_cat_id_lang;

    /**
     * @var string
     *
     * @ORM\Column(name="des_cat_name", type="string", length=255)
     */
    private $des_cat_name;

    /**
     * @ORM\ManyToOne(targetEntity="destinationCategory",inversedBy="")
     * @ORM\JoinColumn(name="des_cat_id_cat",referencedColumnName="des_cat_id")
     */
    private $des_cat_id_cat;


    /**
     * Get des_cat_id
     *
     * @return integer 
     */
    public function getDesCatId()
    {
        return $this->des_cat_id;
    }

    /**
     * Set des_cat_name
     *
     * @param string $desCatName
     * @return destinationCategoryLang
     */
    public function setDesCatName($desCatName)
    {
        $this->des_cat_name = $desCatName;
    
        return $this;
    }

    /**
     * Get des_cat_name
     *
     * @return string 
     */
    public function getDesCatName()
    {
        return $this->des_cat_name;
    }

    /**
     * Set des_cat_id_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $desCatIdLang
     * @return destinationCategoryLang
     */
    public function setDesCatIdLang(\MyCp\mycpBundle\Entity\lang $desCatIdLang = null)
    {
        $this->des_cat_id_lang = $desCatIdLang;
    
        return $this;
    }

    /**
     * Get des_cat_id_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getDesCatIdLang()
    {
        return $this->des_cat_id_lang;
    }

    /**
     * Set des_cat_id_cat
     *
     * @param \MyCp\mycpBundle\Entity\destinationCategory $desCatIdCat
     * @return destinationCategoryLang
     */
    public function setDesCatIdCat(\MyCp\mycpBundle\Entity\destinationCategory $desCatIdCat = null)
    {
        $this->des_cat_id_cat = $desCatIdCat;
    
        return $this;
    }

    /**
     * Get des_cat_id_cat
     *
     * @return \MyCp\mycpBundle\Entity\destinationCategory 
     */
    public function getDesCatIdCat()
    {
        return $this->des_cat_id_cat;
    }
}