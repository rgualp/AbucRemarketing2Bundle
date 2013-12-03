<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * destinationLocation
 *
 * @ORM\Table(name="destinationlocation")
 * @ORM\Entity
 */
class destinationLocation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="des_loc_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $des_loc_id;

    /**
     * @ORM\ManyToOne(targetEntity="destination",inversedBy="")
     * @ORM\JoinColumn(name="des_loc_des_id",referencedColumnName="des_id")
     */
    private $des_loc_destination;

    /**
     * @ORM\ManyToOne(targetEntity="municipality",inversedBy="")
     * @ORM\JoinColumn(name="des_loc_mun_id",referencedColumnName="mun_id")
     */
    private $des_loc_municipality;

    /**
     * @ORM\ManyToOne(targetEntity="province",inversedBy="")
     * @ORM\JoinColumn(name="des_loc_prov_id",referencedColumnName="prov_id")
     */
    private $des_loc_province;

    /**
     * Get des_loc_id
     *
     * @return integer 
     */
    public function getDesLocId()
    {
        return $this->des_loc_id;
    }

    /**
     * Set des_loc_destination
     *
     * @param \MyCp\mycpBundle\Entity\destination $desLocDestination
     * @return destinationLocation
     */
    public function setDesLocDestination(\MyCp\mycpBundle\Entity\destination $desLocDestination = null)
    {
        $this->des_loc_destination = $desLocDestination;
    
        return $this;
    }

    /**
     * Get des_loc_destination
     *
     * @return \MyCp\mycpBundle\Entity\destination 
     */
    public function getDesLocDestination()
    {
        return $this->des_loc_destination;
    }

    /**
     * Set des_loc_municipality
     *
     * @param \MyCp\mycpBundle\Entity\municipality $desLocMunicipality
     * @return destinationLocation
     */
    public function setDesLocMunicipality(\MyCp\mycpBundle\Entity\municipality $desLocMunicipality = null)
    {
        $this->des_loc_municipality = $desLocMunicipality;
    
        return $this;
    }

    /**
     * Get des_loc_municipality
     *
     * @return \MyCp\mycpBundle\Entity\municipality 
     */
    public function getDesLocMunicipality()
    {
        return $this->des_loc_municipality;
    }

    /**
     * Set des_loc_province
     *
     * @param \MyCp\mycpBundle\Entity\province $desLocProvince
     * @return destinationLocation
     */
    public function setDesLocProvince(\MyCp\mycpBundle\Entity\province $desLocProvince = null)
    {
        $this->des_loc_province = $desLocProvince;
    
        return $this;
    }

    /**
     * Get des_loc_province
     *
     * @return \MyCp\mycpBundle\Entity\province 
     */
    public function getDesLocProvince()
    {
        return $this->des_loc_province;
    }
    
    /**
     * Yanet - Inicio
     */
    public function __toString()
    {
        $municipality = $this->getDesLocMunicipality();
        $province = $this->getDesLocProvince();
        return $municipality.(($municipality != null && $province != null)? ' / ':'').$province;
    }
    /**
     * Yanet - Fin
     */
}