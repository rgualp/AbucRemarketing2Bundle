<?php


namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * paTourOperator
 *
 * @ORM\Table(name="pa_tour_operator")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paTourOperatorRepository")
 * @ORM\EntityListeners({"MyCp\PartnerBundle\Listener\BaseEntityListener"})
 *
 */
class paTourOperator extends baseEntity
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="paTravelAgency",inversedBy="tourOperators")
     * @ORM\JoinColumn(name="travel_agency",referencedColumnName="id")
     */
    private $travelAgency;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\user",inversedBy="tourOperators")
     * @ORM\JoinColumn(name="tourOperator",referencedColumnName="user_id")
     */
    private $tourOperator;


    public function __construct() {
        parent::__construct();

    }

    /**
     * @return mixed
     */
    public function getTourOperator()
    {
        return $this->tourOperator;
    }

    /**
     * @param mixed $tourOperator
     * @return mixed
     */
    public function setTourOperator($tourOperator)
    {
        $this->tourOperator = $tourOperator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTravelAgency()
    {
        return $this->travelAgency;
    }

    /**
     * @param mixed $travelAgency
     * @return mixed
     */
    public function setTravelAgency($travelAgency)
    {
        $this->travelAgency = $travelAgency;
        return $this;
    }


}