<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * province
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\provinceRepository")
 */
class province
{
    /**
     * @var integer
     *
     * @ORM\Column(name="prov_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $prov_id;

    /**
     * @var string
     *
     * @ORM\Column(name="prov_name", type="string", length=255)
     */
    private $prov_name;

    /**
     * @var string
     *
     * @ORM\Column(name="prov_phone_code", type="string", length=255)
     */
    private $prov_phone_code;

    /**
     * Get prov_id
     *
     * @return integer 
     */
    public function getProvId()
    {
        return $this->prov_id;
    }

    /**
     * Set prov_name
     *
     * @param string $provName
     * @return province
     */
    public function setProvName($provName)
    {
        $this->prov_name = $provName;
    
        return $this;
    }

    /**
     * Get prov_name
     *
     * @return string 
     */
    public function getProvName()
    {
        return $this->prov_name;
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
        return $this->getProvName();
    }
    /**
     * Codigo Yanet - Fin
     */

    /**
     * Set prov_phone_code
     *
     * @param string $provPhoneCode
     * @return province
     */
    public function setProvPhoneCode($provPhoneCode)
    {
        $this->prov_phone_code = $provPhoneCode;
    
        return $this;
    }

    /**
     * Get prov_phone_code
     *
     * @return string 
     */
    public function getProvPhoneCode()
    {
        return $this->prov_phone_code;
    }
}