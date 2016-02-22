<?php


namespace MyCp\mycpBundle\Entity;

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
     * @ORM\Column(name="icon_or_class_nam", type="string", length=255, nullable=true)
     */
    private $icon_or_class_name;

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



}