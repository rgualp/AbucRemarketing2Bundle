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
     * @var string
     *
     * @ORM\Column(name="pho_notes", type="string", length=255, nullable=true)
     */
    private $pho_notes;


    /**
     * @var bool
     *
     * @ORM\Column(name="cover", type="boolean", nullable=true)
     */
    private $front = false;

    /**
     * @return bool
     */
    public function isFront()
    {
        return $this->front;
    }

    /**
     * @param bool $front
     */
    public function setFront($front)
    {
        $this->front = $front;
    }


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

    /**
     * @return string
     */
    public function getPhoNotes()
    {
        return $this->pho_notes;
    }

    /**
     * @param string $pho_notes
     * @return mixed
     */
    public function setPhoNotes($pho_notes)
    {
        $this->pho_notes = $pho_notes;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPhotoLangs()
    {
        return $this->photo_langs;
    }

    /**
     * @param ArrayCollection $photo_langs
     * @return mixed
     */
    public function setPhotoLangs($photo_langs)
    {
        $this->photo_langs = $photo_langs;
        return $this;
    }

    /*
     * Remove photoLang
     *
     * @param \MyCp\mycpBundle\Entity\photoLang $photoLang
     */
    public function removePhotoLang(\MyCp\mycpBundle\Entity\photoLang $photoLang)
    {
        $this->photo_langs->removeElement($photoLang);
    }
   public function __construct()
   {
       $this->photo_langs=new ArrayCollection();
   }


}