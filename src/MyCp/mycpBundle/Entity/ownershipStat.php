<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipStat
 *
 * @ORM\Table(name="ownershipstat")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\ownershipStatRepository")
 *
 */
class ownershipStat
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
     * @ORM\ManyToOne(targetEntity="municipality",inversedBy="statMunicipalities")
     * @ORM\JoinColumn(name="stat_municipality",referencedColumnName="mun_id", nullable=true)
     */
    private $stat_municipality;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclatorStat",inversedBy="ownershipStats")
     * @ORM\JoinColumn(name="stat_nomenclator",referencedColumnName="nom_id", nullable=true)
     */
    private $stat_nomenclator;

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
     * Set stat_municipality
     *
     * @param municipality $municipality
     * @return $this
     */
    public function setStatMunicipality(municipality $municipality)
    {
        $this->stat_municipality = $municipality;

        return $this;
    }

    /**
     * Get stat_municipality
     *
     * @return municipality
     */
    public function getStatMunicipality()
    {
        return $this->stat_municipality;
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

    public function __toString(){
        return $this->stat_value;
    }

}