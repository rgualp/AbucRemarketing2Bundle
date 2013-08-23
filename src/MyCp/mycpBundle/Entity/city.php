<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * city
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class city
{
    /**
     * @var integer
     *
     * @ORM\Column(name="cit_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $cit_id;

    /**
     * @var string
     *
     * @ORM\Column(name="cit_name", type="string", length=255)
     */
    private $cit_name;

    /**
     * @var string
     *
     * @ORM\Column(name="cit_co_code", type="string", length=255)
     */
    private $cit_co_code;



    /**
     * Get cit_id
     *
     * @return integer 
     */
    public function getCitId()
    {
        return $this->cit_id;
    }

    /**
     * Set cit_name
     *
     * @param string $citName
     * @return city
     */
    public function setCitName($citName)
    {
        $this->cit_name = $citName;
    
        return $this;
    }

    /**
     * Get cit_name
     *
     * @return string 
     */
    public function getCitName()
    {
        return $this->cit_name;
    }

    /**
     * Set cit_co_code
     *
     * @param string $citCoCode
     * @return city
     */
    public function setCitCoCode($citCoCode)
    {
        $this->cit_co_code = $citCoCode;
    
        return $this;
    }

    /**
     * Get cit_co_code
     *
     * @return string 
     */
    public function getCitCoCode()
    {
        return $this->cit_co_code;
    }

    public function __toString()
    {
        return $this->cit_name;
    }
}