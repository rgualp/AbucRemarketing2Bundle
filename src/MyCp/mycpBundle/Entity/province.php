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
     * @var string
     *
     * @ORM\Column(name="prov_code", type="string", length=5, nullable=true)
     */
    private $prov_code;

    /**
     * @var string
     *
     * @ORM\Column(name="prov_own_code", type="string", length=3, nullable=true)
     */
    private $prov_own_code;

    /**
     * @ORM\OneToMany(targetEntity="municipality",mappedBy="mun_prov_id")
     */
    private $prov_municipalities;


    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->prov_municipalities = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get prov_id
     *
     * @return integer
     */
    public function getProvId()
    {
        return $this->prov_id;
    }

    public function getProvMunicipalities()
    {
        return $this->prov_municipalities;
    }

    /**
     * Retur object as string
     * @return string
     */
    public function __toString()
    {
        return $this->getProvName();
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
     * Codigo Yanet - Fin
     */

    /**
     * Get prov_phone_code
     *
     * @return string
     */
    public function getProvPhoneCode()
    {
        return $this->prov_phone_code;
    }

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
     * Get prov_code
     *
     * @return string
     */
    public function getProvCode()
    {
        return $this->prov_code;
    }

    /**
     * Set prov_code
     *
     * @param string $provCode
     * @return province
     */
    public function setProvCode($provCode)
    {
        $this->prov_code = $provCode;

        return $this;
    }

    /**
     * Get prov_own_code
     *
     * @return string
     */
    public function getProvOwnCode()
    {
        return $this->prov_own_code;
    }

    /**
     * Set prov_own_code
     *
     * @param string $provOwnCode
     * @return province
     */
    public function setProvOwnCode($provOwnCode)
    {
        $this->prov_own_code = $provOwnCode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }


}