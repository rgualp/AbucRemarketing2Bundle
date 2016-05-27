<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * photo
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\photoRepository")
 */
class photo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pho_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $pho_id;

    /**
     * @var string
     *
     * @ORM\Column(name="pho_name", type="string", length=255)
     */
    private $pho_name;

    /**
     * @var integer
     *
     * @ORM\Column(name="pho_order", type="integer",nullable=true)
     */
    private $pho_order;
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="photoLang", mappedBy="pho_lang_id_photo")
     */
    protected $photo_langs;


    /**
     * Get pho_id
     *
     * @return integer 
     */
    public function getPhoId()
    {
        return $this->pho_id;
    }

    /**
     * Set pho_name
     *
     * @param string $phoName
     * @return photo
     */
    public function setPhoName($phoName)
    {
        $this->pho_name = $phoName;
    
        return $this;
    }

    /**
     * Get pho_name
     *
     * @return string 
     */
    public function getPhoName()
    {
        return $this->pho_name;
    }

    /**
     * Set pho_order
     *
     * @param integer $phoOrder
     * @return photo
     */
    public function setPhoOrder($phoOrder)
    {
        $this->pho_order = $phoOrder;
    
        return $this;
    }

    /**
     * Get pho_order
     *
     * @return integer 
     */
    public function getPhoOrder()
    {
        return $this->pho_order;
    }

    public function __construct()
    {
        $this->photo_langs=new ArrayCollection();
    }

    /**
     * Add photoLang
     *
     * @param \MyCp\mycpBundle\Entity\photoLang $photoLang
     *
     * @return photo
     */
    public function addPhotoLang(\MyCp\mycpBundle\Entity\photoLang $photoLang)
    {
        $this->photo_langs[] = $photoLang;

        return $this;
    }

    /**
     * Remove photoLang
     *
     * @param \MyCp\mycpBundle\Entity\photoLang $photoLang
     */
    public function removePhotoLang(\MyCp\mycpBundle\Entity\photoLang $photoLang)
    {
        $this->photo_langs->removeElement($photoLang);
    }

    /**
     * Get photoLangs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhotoLangs()
    {
        return $this->photo_langs;
    }
}
