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
     * @ORM\Column(name="des_icon", type="string", length=255,nullable=true)
     */
    private $des_icon;

    /**
     * @var string
     *
     * @ORM\Column(name="des_icon_prov_map", type="string", length=255,nullable=true)
     */
    private $des_icon_prov_map;

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
     * Set des_icon
     *
     * @param string $desIcon
     * @return destinationCategory
     */
    public function setDesIcon($desIcon)
    {
        $this->des_icon = $desIcon;
    
        return $this;
    }

    /**
     * Get des_icon
     *
     * @return string 
     */
    public function getDesIcon()
    {
        return $this->des_icon;
    }

    /**
     * Set des_icon_prov_map
     *
     * @param string $desIconProvMap
     * @return destinationCategory
     */
    public function setDesIconProvMap($desIconProvMap)
    {
        $this->des_icon_prov_map = $desIconProvMap;
    
        return $this;
    }

    /**
     * Get des_icon_prov_map
     *
     * @return string 
     */
    public function getDesIconProvMap()
    {
        return $this->des_icon_prov_map;
    }
}