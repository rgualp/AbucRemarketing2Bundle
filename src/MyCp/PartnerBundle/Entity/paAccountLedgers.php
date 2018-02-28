<?php

namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * paAccountLedgers
 *
 * @ORM\Table(name="pa_account_ledgers")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paLedgersRepository")
 *
 *
 */
class paAccountLedgers {

    /**
     * @var integer
     *
     * @ORM\Column(name="ledger_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $ledger_id;

    /**
     * @var string
     *
     * @ORM\Column(name="cas", type="text",length=200)
     */
    private $cas;

    /**
     * @return string
     */
    public function getCas()
    {
        return $this->cas;
    }

    /**
     * @param string $cas
     */
    public function setCas($cas)
    {
        $this->cas = $cas;
    }

    /**
     * @return float
     */
    public function getDebit()
    {
        if($this->debit==null){
            return 0;
        }
        else {
            return $this->debit;
        }
    }

    /**
     * @param float $debit
     */
    public function setDebit($debit,$balance)
    {
        $this->debit = $debit;
        $this->balance = $balance + $debit;



        return $this->balance;
    }

    /**
     * @return float
     */
    public function getCredit()
    {
        if($this->credit==null){
            return 0;
        }
        else{
        return $this->credit;}
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @param float $credit
     */
    public function setCredit($credit,$balance)
    {
        $this->credit = $credit;
        $this->balance = $balance - $credit;
        return $this->balance;
    }
    /**
     *
     * @ORM\ManyToOne(targetEntity="paAccount",inversedBy="ledgers")
     * @ORM\JoinColumn(name="account",referencedColumnName="account_id")
     */
    private $account;

    /**
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created->format('d-m-Y h:i:s');
    }

    /**
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    /**
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @var float
     *
     * @ORM\Column(name="balance", type="float")
     */
    private $balance;


    /**
     * @var float
     *
     * @ORM\Column(name="debit", type="float",nullable=true)
     */
    private $debit;


    /**
     * @var float
     *
     * @ORM\Column(name="credit", type="float" ,nullable=true)
     */
    private $credit;


    /**
     * @var datetime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=500)
     */
    private $description;

    /**
     * paAccountLedgerconstructor.
     *
     */
    public function __construct()
    {
        $this->created = new \DateTime();

    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->ledger_id;
    }

}
