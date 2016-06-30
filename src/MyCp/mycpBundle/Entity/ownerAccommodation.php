<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * faq
 *
 * @ORM\Table(name="owneraccommodation")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\ownerAccommodationRepository")
 */
class ownerAccommodation
{

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="owners")
     * @ORM\Id
     * @ORM\JoinColumn(name="accommodation",referencedColumnName="own_id")
     */
    private $accommodation;

    /**
     * @ORM\ManyToOne(targetEntity="owner",inversedBy="accommodations")
     * @ORM\Id
     * @ORM\JoinColumn(name="owner",referencedColumnName="id")
     */
    private $owner;

    /**
     * @return mixed
     */
    public function getAccommodation()
    {
        return $this->accommodation;
    }

    /**
     * @param mixed $accommodation
     * @return mixed
     */
    public function setAccommodation($accommodation)
    {
        $this->accommodation = $accommodation;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     * @return mixed
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }



}