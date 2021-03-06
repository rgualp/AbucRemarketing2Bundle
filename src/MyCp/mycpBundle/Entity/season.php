<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * season
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\seasonRepository")
 *
 */
class season
{
    /**
     * All allowed season's types
     */
    const SEASON_TYPE_LOW = 0;
    const SEASON_TYPE_HIGH = 1;
    const SEASON_TYPE_SPECIAL = 2;

    /**
     * Contains all possible statuses
     *
     * @var array
     */
    private $season_types = array(
        self::SEASON_TYPE_LOW,
        self::SEASON_TYPE_HIGH,
        self::SEASON_TYPE_SPECIAL,
    );

    /**
     * @var integer
     *
     * @ORM\Column(name="season_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $season_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="season_type", type="integer")
     */
    private $season_type;

    /**
     * @var datetime
     *
     * @ORM\Column(name="season_startdate", type="datetime")
     * @Assert\NotBlank()
     */
    private $season_startdate;

    /**
     * @var datetime
     *
     * @ORM\Column(name="season_enddate", type="datetime")
     * @Assert\NotBlank()
     */
    private $season_enddate;

    /**
     * @ORM\ManyToOne(targetEntity="destination",inversedBy="")
     * @ORM\JoinColumn(name="season_destination",referencedColumnName="des_id", nullable=true)
     */
    private $season_destination;

    /**
     * @var string
     *
     * @ORM\Column(name="season_reason", type="string", nullable=true)
     */
    private $season_reason;

    /**
     * Constructor
     */
    public function __construct() {
        $this->season_type = season::SEASON_TYPE_LOW;
    }

    /**
     * Get season_id
     *
     * @return integer
     */
    public function getSeasonId()
    {
        return $this->season_id;
    }

    /**
     * Set season_type
     *
     * @param integer $seasonType
     * @return season
     */
    public function setSeasonType($seasonType)
    {
        if (!in_array($seasonType, $this->season_types)) {
            throw new \InvalidArgumentException("Season type $seasonType not allowed");
        }

        $this->season_type = $seasonType;
        return $this;
    }

    /**
     * Get season_type
     *
     * @return integer
     */
    public function getSeasonType()
    {
        return $this->season_type;
    }

    /**
     * Set season_startdate
     *
     * @param datetime $startDate
     * @return season
     */
    public function setSeasonStartDate($startDate)
    {
        $this->season_startdate = $startDate;

        return $this;
    }

    /**
     * Get season_startdate
     *
     * @return datetime
     */
    public function getSeasonStartDate()
    {
        return $this->season_startdate;
    }

    /**
     * Set season_enddate
     *
     * @param datetime $endDate
     * @return season
     */
    public function setSeasonEndDate($endDate)
    {
        $this->season_enddate = $endDate;

        return $this;
    }

    /**
     * Get season_enddate
     *
     * @return datetime
     */
    public function getSeasonEndDate()
    {
        return $this->season_enddate;
    }

    /**
     * Set season_destination
     *
     * @param \MyCp\mycpBundle\Entity\destination $destination
     * @return season
     */
    public function setSeasonDestination($destination = null)
    {
        $this->season_destination = $destination;

        return $this;
    }

    /**
     * Get season_destination
     *
     * @return \MyCp\mycpBundle\Entity\destination
     */
    public function getSeasonDestination()
    {
        return $this->season_destination;
    }

    /**
     * Set season_reason
     *
     * @param string $reason
     * @return season
     */
    public function setSeasonReason($reason)
    {
        $this->season_reason = $reason;

        return $this;
    }

    /**
     * Get season_reason
     *
     * @return string
     */
    public function getSeasonReason()
    {
        return $this->season_reason;
    }

    public static function getSeasonTypes()
    {
        $s_types = array();
        $s_types[season::SEASON_TYPE_HIGH] = "Temporada Alta";
        $s_types[season::SEASON_TYPE_SPECIAL] = "Temporada Especial";
        return $s_types;
    }

    public function getSeasonName()
    {
        switch($this->getSeasonType())
        {
            case season::SEASON_TYPE_SPECIAL: return "Temporada Especial";
            case season::SEASON_TYPE_HIGH: return "Temporada Alta";
            case season::SEASON_TYPE_LOW: return "Temporada Baja";
        }
    }

    /*Logs functions*/
    public function getLogDescription()
    {
        return $this->getSeasonName().": ".$this->getSeasonStartDate()->format("d/m/Y")." - ".$this->getSeasonEndDate()->format("d/m/Y");
    }
}