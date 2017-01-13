<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * cancelType
 *
 * @ORM\Table(name="cancel_type")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\cancelTypeRepository")
 */
class cancelType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="cancel_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $cancel_id;

    /**
     * @var string
     *
     * @ORM\Column(name="cancel_name", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $cancel_name;


    public function __toString()
    {
        return $this->cancel_name;
    }


    /**
     * Get cancel_id
     *
     * @return integer 
     */
    public function getCancelId()
    {
        return $this->cancel_id;
    }

    /**
     * Set cancel_name
     *
     * @param string $cancelName
     * @return cancelType
     */
    public function setCancelName($cancelName)
    {
        $this->cancel_name = $cancelName;
    
        return $this;
    }

    /**
     * Get cancel_name
     *
     * @return string 
     */
    public function getCancelName()
    {
        return $this->cancel_name;
    }

}
