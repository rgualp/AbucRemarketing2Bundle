<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * nomenclatorStat
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\nomenclatorStatRepository")
 *
 */
class nomenclatorStat
{
    /**
     * @var integer
     *
     * @ORM\Column(name="nom_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $nom_id;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclatorStat",inversedBy="")
     * @ORM\JoinColumn(name="nom_parent",referencedColumnName="nom_id", nullable=true)
     */
    private $nom_parent;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_name", type="string")
     */
    private $nom_name;


    /**
     * Get nom_id
     *
     * @return integer
     */
    public function getNomId()
    {
        return $this->nom_id;
    }

    /**
     * Set nom_parent
     *
     * @param string $parent
     * @return nomenclatorStat
     */
    public function setNomParent($parent)
    {
        $this->nom_parent = $parent;

        return $this;
    }

    /**
     * Get nom_parent
     *
     * @return nomenclatorStat
     */
    public function getNomParent()
    {
        return $this->nom_parent;
    }

    /**
     * Set nom_name
     *
     * @param string $nomName
     * @return nomenclatorStat
     */
    public function setNomName($nomName)
    {
        $this->nom_name = $nomName;

        return $this;
    }

    /**
     * Get nom_name
     *
     * @return string
     */
    public function getNomName()
    {
        return $this->nom_name;
    }

}