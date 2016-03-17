<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * award
 *
 * @ORM\Table()
 * @ORM\Entity
 *
 */
class award
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var decimal
     *
     * @ORM\Column(name="ranking_value", type="decimal")
     */
    private $ranking_value;

    /**
     * @var string
     *
     * @ORM\Column(name="icon_or_class_name", type="string", length=255, nullable=true)
     */
    private $icon_or_class_name;

    /**
     * @var string
     *
     * @ORM\Column(name="second_icon_or_class_name", type="string", length=255, nullable=true)
     */
    private $second_icon_or_class_name;

    /**
     * @ORM\OneToMany(targetEntity="accommodationAward", mappedBy="award")
     */
    private $awardAccommodations;

    public function __construct() {
        $this->awardAccommodations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIconOrClassName()
    {
        return $this->icon_or_class_name;
    }

    /**
     * @param string $icon_or_class_name
     * @return this
     */
    public function setIconOrClassName($icon_or_class_name)
    {
        $this->icon_or_class_name = $icon_or_class_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getRankingValue()
    {
        return $this->ranking_value;
    }

    /**
     * @param decimal $ranking_value
     * @return this
     */
    public function setRankingValue($ranking_value)
    {
        $this->ranking_value = $ranking_value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAwardAccommodations()
    {
        return $this->awardAccommodations;
    }

    /**
     * @param mixed $awardAccommodations
     * @return this
     */
    public function setAwardAccommodations($awardAccommodations)
    {
        $this->awardAccommodations = $awardAccommodations;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecondIconOrClassName()
    {
        return $this->second_icon_or_class_name;
    }

    /**
     * @param string $second_icon_or_class_name
     * @return this
     */
    public function setSecondIconOrClassName($second_icon_or_class_name)
    {
        $this->second_icon_or_class_name = $second_icon_or_class_name;
        return $this;
    }



}