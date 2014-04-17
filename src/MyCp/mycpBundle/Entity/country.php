<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * country
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\countryRepository")
 */
class country
{
    /**
     * @var integer
     *
     * @ORM\Column(name="co_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $co_id;

    /**
     * @var string
     *
     * @ORM\Column(name="co_code", type="string", length=255)
     */
    private $co_code;

    /**
     * @var string
     *
     * @ORM\Column(name="co_name", type="string", length=255)
     */
    private $co_name;


    /**
     * Get co_id
     *
     * @return integer 
     */
    public function getCoId()
    {
        return $this->co_id;
    }

    /**
     * Set co_code
     *
     * @param string $coCode
     * @return country
     */
    public function setCoCode($coCode)
    {
        $this->co_code = $coCode;
    
        return $this;
    }

    /**
     * Get co_code
     *
     * @return string 
     */
    public function getCoCode()
    {
        return $this->co_code;
    }

    /**
     * Set co_name
     *
     * @param string $coName
     * @return country
     */
    public function setCoName($coName)
    {
        $this->co_name = $coName;
    
        return $this;
    }

    /**
     * Get co_name
     *
     * @return string 
     */
    public function getCoName()
    {
        return $this->co_name;
    }

    public function __toString()
    {
        return $this->co_name;
    }
}