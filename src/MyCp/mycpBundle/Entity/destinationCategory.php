<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * destinationCategory
 *
 * @ORM\Table(name="destinationcategory")
 * @ORM\Entity()
 *
 */
class destinationCategory
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
     * @var string
     *
     * @ORM\Column(name="des_cat_name", type="string", length=255)
     */
    private $des_cat_name;

    /**
     * @var string
     *
     * @ORM\Column(name="des_cat_name_spanish", type="string", length=255)
     */
    private $des_cat_name_spanish;

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
     * @return destinationCategory
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
     * Set des_cat_name_spanish
     *
     * @param string $desCatNameSpanish
     * @return destinationCategory
     */
    public function setDesCatNameSpanish($desCatNameSpanish)
    {
        $this->des_cat_name_spanish = $desCatNameSpanish;
    
        return $this;
    }

    /**
     * Get des_cat_name_spanish
     *
     * @return string 
     */
    public function getDesCatNameSpanish()
    {
        return $this->des_cat_name_spanish;
    }
}