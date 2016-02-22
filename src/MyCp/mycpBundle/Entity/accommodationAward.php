<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * accommodationAward
 *
 * @ORM\Table(name="accommodation_award")
 * @ORM\Entity
 *
 */
class accommodationAward
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="award",inversedBy="awardAccommodations")
     * @ORM\JoinColumn(name="award",referencedColumnName="id")
     */
    private $award;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="")
     * @ORM\JoinColumn(name="accommodation",referencedColumnName="own_id")
     */
    private $accommodation;

    /**
     * @var date
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @return mixed
     */
    public function getAccommodation()
    {
        return $this->accommodation;
    }

    /**
     * @param mixed $accommodation
     * @return this
     */
    public function setAccommodation($accommodation)
    {
        $this->accommodation = $accommodation;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAward()
    {
        return $this->award;
    }

    /**
     * @param mixed $award
     * @return this
     */
    public function setAward($award)
    {
        $this->award = $award;
        return $this;
    }

    /**
     * @return date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param date $date
     * @return this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }


}