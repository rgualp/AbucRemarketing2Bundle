<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * transferMethodPayment
 *
 * @ORM\Table(name="transfer_method_payment")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\transferMethodPaymentRepository")
 *
 */
class transferMethodPayment
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
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="transferMethodsPayment")
     * @ORM\JoinColumn(name="accommodation",referencedColumnName="own_id")
     */
    private $accommodation;

    /**
     * @var string
     *
     * @ORM\Column(name="titular", type="string")
     */
    private $titular;

    /**
     * @var string
     *
     * @ORM\Column(name="accountNumber", type="string")
     */
    private $accountNumber;


    /**
     * @ORM\ManyToOne(targetEntity="nomenclator",inversedBy="")
     * @ORM\JoinColumn(name="accountType",referencedColumnName="nom_id")
     */
    private $accountType;

    /**
     * @var string
     *
     * @ORM\Column(name="bankBranchName", type="string")
     */
    private $bankBranchName;

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
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     * @return mixed
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @param mixed $accountType
     * @return mixed
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
        return $this;
    }

    /**
     * @return string
     */
    public function getBankBranchName()
    {
        return $this->bankBranchName;
    }

    /**
     * @param string $bankBranchName
     * @return mixed
     */
    public function setBankBranchName($bankBranchName)
    {
        $this->bankBranchName = $bankBranchName;
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
    public function getTitular()
    {
        return $this->titular;
    }

    /**
     * @param string $titular
     * @return mixed
     */
    public function setTitular($titular)
    {
        $this->titular = $titular;
        return $this;
    }



}