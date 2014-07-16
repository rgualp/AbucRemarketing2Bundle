<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * municipality
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\municipalityRepository")
 */
class municipality
{
    /**
     * @var integer
     *
     * @ORM\Column(name="mun_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $mun_id;

    /**
     * @ORM\ManyToOne(targetEntity="province", inversedBy="prov_municipalities")
     * @ORM\JoinColumn(name="mun_prov_id", referencedColumnName="prov_id")
     * @return integer
     */
    private $mun_prov_id;

    /**
     * @var string
     *
     * @ORM\Column(name="mun_name", type="string", length=255)
     */
    private $mun_name;
    
        
    /**
     * Get mun_id
     *
     * @return integer 
     */
    public function getMunId()
    {
        return $this->mun_id;
    }
    

    /**
     * Set mun_name
     *
     * @param string $munName
     * @return municipality
     */
    public function setMunName($munName)
    {
        $this->mun_name = $munName;
    
        return $this;
    }

    /**
     * Get mun_name
     *
     * @return string 
     */
    public function getMunName()
    {
        return $this->mun_name;
    }

    /**
     * Set mun_prov_id
     *
     * @param \MyCp\mycpBundle\Entity\province $munProvId
     * @return municipality
     */
    public function setMunProvId(\MyCp\mycpBundle\Entity\province $munProvId = null)
    {
        $this->mun_prov_id = $munProvId;
    
        return $this;
    }

    /**
     * Get mun_prov_id
     *
     * @return \MyCp\mycpBundle\Entity\province 
     */
    public function getMunProvId()
    {
        return $this->mun_prov_id;
    }

    /**
     * Codigo Yanet - Inicio
     */
    /**
     * Retur object as string
     * @return string
     */
    public function __toString()
    {
        return $this->getMunName();
    }
    /**
     * Codigo Yanet - Fin
     */
}