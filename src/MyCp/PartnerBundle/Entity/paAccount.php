<?php

namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * paAccount
 *
 * @ORM\Table(name="pa_account")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paAccountRepository")
 *
 *
 */
class paAccount {
    /**
     * @return mixed
     */
    public function getLedgers()
    {
        return $this->ledgers;
    }

    /**
     * Set ledgers
     *
     * @return mixed
     */
    public function setLedgers($ledgers)
    {
        $this->ledgers = $ledgers;
        return $this;
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="account_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $account_id;

    /**
     * @ORM\OneToMany(targetEntity="paAccountLedgers", mappedBy="account")
     */
    private $ledgers;

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
     * paAccount constructor.
     *
     */
    public function __construct()
    {
        $this->ledgers = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->account_id;
    }

}
