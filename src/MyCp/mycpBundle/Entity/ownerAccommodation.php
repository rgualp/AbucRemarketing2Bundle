<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * faq
 *
 * @ORM\Table(name="ownerAccommodation")
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



}