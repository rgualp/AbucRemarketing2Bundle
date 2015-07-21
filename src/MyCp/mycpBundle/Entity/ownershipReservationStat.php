<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipStat
 *
 * @ORM\Table(name="ownershipreservationstat")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\ownershipReservationStatRepository")
 *
 */
class ownershipReservationStat
{
    /**
     * @var integer
     *
     * @ORM\Column(name="stat_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $stat_id;

    /**
     * @ORM\ManyToOne(targetEntity="ownership")
     * @ORM\JoinColumn(name="stat_accommodation",referencedColumnName="own_id")
     */
    private $stat_accommodation;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclatorStat")
     * @ORM\JoinColumn(name="stat_nomenclator",referencedColumnName="nom_id", nullable=true)
     */
    private $stat_nomenclator;

    /**
     * @var date
     *
     * @ORM\Column(name="stat_date_from", type="date")
     */
    private $stat_date_from;

    /**
     * @var date
     *
     * @ORM\Column(name="stat_date_to", type="date")
     */
    private $stat_date_to;

    /**
     * @var string
     *
     * @ORM\Column(name="stat_value", type="string")
     */
    private $stat_value;


    /**
     * Get stat_id
     *
     * @return integer
     */
    public function getStatId()
    {
        return $this->stat_id;
    }

    /**
     * Set stat_accommodation
     *
     * @param ownership $accommodation
     * @return $this
     */
    public function setStatAccommodation(ownership $accommodation)
    {
        $this->stat_accommodation = $accommodation;

        return $this;
    }

    /**
     * Get stat_accommodation
     *
     * @return ownership
     */
    public function getStatAccommodation()
    {
        return $this->stat_accommodation;
    }

    /**
     * Set stat_nomenclator
     *
     * @param nomenclatorStat $nomenclatorStat
     * @return $this
     */
    public function setStatNomenclator(nomenclatorStat $nomenclatorStat)
    {
        $this->stat_nomenclator = $nomenclatorStat;

        return $this;
    }

    /**
     * Get stat_nomenclator
     *
     * @return nomenclatorStat
     */
    public function getStatNomenclator()
    {
        return $this->stat_nomenclator;
    }

    /**
     * Get stat_value
     *
     * @return string
     */
    public function getStatValue()
    {
        return $this->stat_value;
    }

    /**
     * Set stat_value
     * @param $value
     * @return $this
     */
    public function setStatValue($value)
    {
        $this->stat_value = $value;
        return $this;
    }

    /**
     * Get stat_date_from
     *
     * @return string
     */
    public function getStatDateFrom()
    {
        return $this->stat_date_from;
    }

    /**
     * Set stat_date_from
     * @param $value
     * @return $this
     */
    public function setStatDateFrom($value)
    {
        $this->stat_date_from = $value;
        return $this;
    }

    /**
     * Get stat_date_to
     *
     * @return string
     */
    public function getStatDateTo()
    {
        return $this->stat_date_to;
    }

    /**
     * Set stat_date_to
     * @param $value
     * @return $this
     */
    public function setStatDateTo($value)
    {
        $this->stat_date_to = $value;
        return $this;
    }

    public function __toString(){
        return $this->stat_value;
    }

}