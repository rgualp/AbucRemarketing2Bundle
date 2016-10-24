<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipRankingExtra
 *
 * @ORM\Table(name="ownership_ranking_extra")
 * @ORM\Entity
 *
 */
class ownershipRankingExtra
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
     * @ORM\OneToOne(targetEntity="ownership",inversedBy="rankingExtra")
     * @ORM\JoinColumn(name="accommodation",referencedColumnName="own_id")
     */
    private $accommodation;

    /**
     * @var datetime
     *
     * @ORM\Column(name="updatedDate", type="date")
     */
    private $updatedDate;

    /**
     * @var int
     *
     * @ORM\Column(name="uDetailsUpdated", type="integer", nullable=true)
     */
    private $uDetailsUpdated;

    /**
     * @var int
     *
     * @ORM\Column(name="fad", type="integer", nullable=true)
     */
    private $fad;

    /**
     * @var int
     *
     * @ORM\Column(name="rRrI", type="integer", nullable=true)
     */
    private $rRrI;

    /**
     * @var int
     *
     * @ORM\Column(name="rsm", type="integer", nullable=true)
     */
    private $rsm;

    /**
     * @var int
     *
     * @ORM\Column(name="acv", type="integer", nullable=true)
     */
    private $acv;


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
     * @return int
     */
    public function getAcv()
    {
        return $this->acv;
    }

    /**
     * @param int $acv
     * @return mixed
     */
    public function setAcv($acv)
    {
        $this->acv = $acv;
        return $this;
    }

    /**
     * @return int
     */
    public function getFad()
    {
        return $this->fad;
    }

    /**
     * @param int $fad
     * @return mixed
     */
    public function setFad($fad)
    {
        $this->fad = $fad;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getRRrI()
    {
        return $this->rRrI;
    }

    /**
     * @param int $rRrI
     * @return mixed
     */
    public function setRRrI($rRrI)
    {
        $this->rRrI = $rRrI;
        return $this;
    }

    /**
     * @return int
     */
    public function getRsm()
    {
        return $this->rsm;
    }

    /**
     * @param int $rsm
     * @return mixed
     */
    public function setRsm($rsm)
    {
        $this->rsm = $rsm;
        return $this;
    }

    /**
     * @return int
     */
    public function getUDetailsUpdated()
    {
        return $this->uDetailsUpdated;
    }

    /**
     * @param int $uDetailsUpdated
     * @return mixed
     */
    public function setUDetailsUpdated($uDetailsUpdated)
    {
        $this->uDetailsUpdated = $uDetailsUpdated;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * @param datetime $updatedDate
     * @return mixed
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;
        return $this;
    }



}