<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * faq
 *
 * @ORM\Table(name="servicefee")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\serviceFeeRepository")
 */
class serviceFee
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
     * @var DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var decimal
     *
     * @ORM\Column(name="fixedFee", type="decimal")
     */
    private $fixedFee;

    /**
     * @var decimal
     *
     * @ORM\Column(name="one_nr_until_20_percent", type="decimal")
     */
    private $one_nr_until_20_percent;

    /**
     * @var decimal
     *
     * @ORM\Column(name="one_nr_from_20_to_25_percent", type="decimal")
     */
    private $one_nr_from_20_to_25_percent;

    /**
     * @var decimal
     *
     * @ORM\Column(name="one_nr_from_more_25_percent", type="decimal")
     */
    private $one_nr_from_more_25_percent;

    /**
     * @var decimal
     *
     * @ORM\Column(name="one_night_several_rooms_percent", type="decimal")
     */
    private $one_night_several_rooms_percent;

    /**
     * @var decimal
     *
     * @ORM\Column(name="one_2_nights_percent", type="decimal")
     */
    private $one_2_nights_percent;

    /**
     * @var decimal
     *
     * @ORM\Column(name="one_3_nights_percent", type="decimal")
     */
    private $one_3_nights_percent;

    /**
     * @var decimal
     *
     * @ORM\Column(name="one_4_nights_percent", type="decimal")
     */
    private $one_4_nights_percent;

    /**
     * @var decimal
     *
     * @ORM\Column(name="one_5_nights_percent", type="decimal")
     */
    private $one_5_nights_percent;

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return mixed
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getFixedFee()
    {
        return $this->fixedFee;
    }

    /**
     * @param decimal $fixedFee
     * @return mixed
     */
    public function setFixedFee($fixedFee)
    {
        $this->fixedFee = $fixedFee;
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
     * @return decimal
     */
    public function getOne2NightsPercent()
    {
        return $this->one_2_nights_percent;
    }

    /**
     * @param decimal $one_2_nights_percent
     * @return mixed
     */
    public function setOne2NightsPercent($one_2_nights_percent)
    {
        $this->one_2_nights_percent = $one_2_nights_percent;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getOne3NightsPercent()
    {
        return $this->one_3_nights_percent;
    }

    /**
     * @param decimal $one_3_nights_percent
     * @return mixed
     */
    public function setOne3NightsPercent($one_3_nights_percent)
    {
        $this->one_3_nights_percent = $one_3_nights_percent;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getOne4NightsPercent()
    {
        return $this->one_4_nights_percent;
    }

    /**
     * @param decimal $one_4_nights_percent
     * @return mixed
     */
    public function setOne4NightsPercent($one_4_nights_percent)
    {
        $this->one_4_nights_percent = $one_4_nights_percent;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getOne5NightsPercent()
    {
        return $this->one_5_nights_percent;
    }

    /**
     * @param decimal $one_5_nights_percent
     * @return mixed
     */
    public function setOne5NightsPercent($one_5_nights_percent)
    {
        $this->one_5_nights_percent = $one_5_nights_percent;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getOneNightSeveralRoomsPercent()
    {
        return $this->one_night_several_rooms_percent;
    }

    /**
     * @param decimal $one_night_several_rooms_percent
     * @return mixed
     */
    public function setOneNightSeveralRoomsPercent($one_night_several_rooms_percent)
    {
        $this->one_night_several_rooms_percent = $one_night_several_rooms_percent;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getOneNrFrom20To25Percent()
    {
        return $this->one_nr_from_20_to_25_percent;
    }

    /**
     * @param decimal $one_nr_from_20_to_25_percent
     * @return mixed
     */
    public function setOneNrFrom20To25Percent($one_nr_from_20_to_25_percent)
    {
        $this->one_nr_from_20_to_25_percent = $one_nr_from_20_to_25_percent;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getOneNrFromMore25Percent()
    {
        return $this->one_nr_from_more_25_percent;
    }

    /**
     * @param decimal $one_nr_from_more_25_percent
     * @return mixed
     */
    public function setOneNrFromMore25Percent($one_nr_from_more_25_percent)
    {
        $this->one_nr_from_more_25_percent = $one_nr_from_more_25_percent;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getOneNrUntil20Percent()
    {
        return $this->one_nr_until_20_percent;
    }

    /**
     * @param decimal $one_nr_until_20_percent
     * @return mixed
     */
    public function setOneNrUntil20Percent($one_nr_until_20_percent)
    {
        $this->one_nr_until_20_percent = $one_nr_until_20_percent;
        return $this;
    }


}