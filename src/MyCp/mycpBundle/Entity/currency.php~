<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * currency
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class currency
{
    /**
     * @var integer
     *
     * @ORM\Column(name="curr_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $curr_id;

    /**
     * @var string
     *
     * @ORM\Column(name="curr_name", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\MaxLength(255)
     */
    private $curr_name;

    /**
     * @var string
     *
     * @ORM\Column(name="curr_code", type="string", length=5)
     * @Assert\NotBlank()
     * @Assert\MaxLength(5)
     */
    private $curr_code;

    /**
     * @var string
     *
     * @ORM\Column(name="curr_symbol", type="string", length=5)
     * @Assert\NotBlank()
     * @Assert\MaxLength(5)
     */
    private $curr_symbol;

    /**
     * @var string
     *
     * @ORM\Column(name="curr_cuc_change", type="decimal",scale=2)
     * @Assert\NotBlank()
     */
    private $curr_cuc_change;


    public function __toString()
    {
        return $this->curr_name;
    }


    /**
     * Get curr_id
     *
     * @return integer 
     */
    public function getCurrId()
    {
        return $this->curr_id;
    }

    /**
     * Set curr_name
     *
     * @param string $currName
     * @return currency
     */
    public function setCurrName($currName)
    {
        $this->curr_name = $currName;
    
        return $this;
    }

    /**
     * Get curr_name
     *
     * @return string 
     */
    public function getCurrName()
    {
        return $this->curr_name;
    }

    /**
     * Set curr_code
     *
     * @param string $currCode
     * @return currency
     */
    public function setCurrCode($currCode)
    {
        $this->curr_code = $currCode;
    
        return $this;
    }

    /**
     * Get curr_code
     *
     * @return string 
     */
    public function getCurrCode()
    {
        return $this->curr_code;
    }

    /**
     * Set curr_symbol
     *
     * @param string $currSymbol
     * @return currency
     */
    public function setCurrSymbol($currSymbol)
    {
        $this->curr_symbol = $currSymbol;
    
        return $this;
    }

    /**
     * Get curr_symbol
     *
     * @return string 
     */
    public function getCurrSymbol()
    {
        return $this->curr_symbol;
    }

    /**
     * Set curr_cuc_change
     *
     * @param float $currCucChange
     * @return currency
     */
    public function setCurrCucChange($currCucChange)
    {
        $this->curr_cuc_change = $currCucChange;
    
        return $this;
    }

    /**
     * Get curr_cuc_change
     *
     * @return float 
     */
    public function getCurrCucChange()
    {
        return $this->curr_cuc_change;
    }
}