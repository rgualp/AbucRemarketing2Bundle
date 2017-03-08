<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * effectiveMethodPayment
 *
 * @ORM\Table(name="effective_method_payment")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\effectiveMethodPaymentRepository")
 *
 */
class effectiveMethodPayment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="effectiveMethodsPayment")
     * @ORM\JoinColumn(name="accommodation",referencedColumnName="own_id")
     */
    private $accommodation;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_name", type="string")
     */
    private $contactName;

    /**
     * @var string
     *
     * @ORM\Column(name="identityNumber", type="string")
     */
    private $identityNumber;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


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
     * @return string
     */
    public function getIdentityNumber()
    {
        return $this->identityNumber;
    }

    /**
     * @param string $identityNumber
     * @return mixed
     */
    public function setIdentityNumber($identityNumber)
    {
        $this->identityNumber = $identityNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * @param string $contactName
     * @return mixed
     */
    public function setContactName($contactName)
    {
        $this->contactName = $contactName;
        return $this;
    }





}