<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * config
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class config
{
    /**
     * @var integer
     *
     * @ORM\Column(name="conf_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $conf_id;

    /**
     * @var string
     *
     * @ORM\Column(name="conf_service_cost", type="string", length=255)
     */
    private $conf_service_cost;

    /**
     * Get conf_id
     *
     * @return integer 
     */
    public function getConfId()
    {
        return $this->conf_id;
    }

    /**
     * Set conf_service_cost
     *
     * @param string $confServiceCost
     * @return config
     */
    public function setConfServiceCost($confServiceCost)
    {
        $this->conf_service_cost = $confServiceCost;
    
        return $this;
    }

    /**
     * Get conf_service_cost
     *
     * @return string 
     */
    public function getConfServiceCost()
    {
        return $this->conf_service_cost;
    }
}