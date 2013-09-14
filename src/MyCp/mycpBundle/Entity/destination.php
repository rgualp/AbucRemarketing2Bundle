<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * destination
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\destinationRepository")
 */
class destination {

    /**
     * @var integer
     *
     * @ORM\Column(name="des_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $des_id;

    /**
     * @var string
     *
     * @ORM\Column(name="des_name", type="string", length=255)
     */
    private $des_name;

    /**
     * @var integer
     *
     * @ORM\Column(name="des_order", type="integer")
     */
    private $des_order;

    /**
     * @var boolean
     *
     * @ORM\Column(name="des_active", type="boolean")
     */
    private $des_active;

    /**
     * @ORM\OneToMany(targetEntity="destinationLang",mappedBy="des_lang_destination")
     */
    private $destinationsLang;

    /**
     * @ORM\ManyToMany(targetEntity="municipality")
     * @ORM\JoinTable(name="destinationlocation",
     *  joinColumns={@ORM\JoinColumn(name="des_loc_des_id", referencedColumnName="des_id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="des_loc_mun_id", referencedColumnName="mun_id")})
     */
    private $destinationsMunicipality;

    /**
     * @ORM\ManyToMany(targetEntity="photo")
     * @ORM\JoinTable(name="destinationphoto",
     *  joinColumns={@ORM\JoinColumn(name="des_pho_des_id", referencedColumnName="des_id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="des_pho_pho_id", referencedColumnName="pho_id")})
     */
    private $destinationsPhoto;

    /**
     * Constructor
     */
    public function __construct() {
        $this->destinationsLang = new ArrayCollection();
    }

    /**
     * Get des_id
     *
     * @return integer 
     */
    public function getDesId() {
        return $this->des_id;
    }

    /**
     * Set des_name
     *
     * @param string $desName
     * @return destination
     */
    public function setDesName($desName) {
        $this->des_name = $desName;

        return $this;
    }

    /**
     * Get des_name
     *
     * @return string 
     */
    public function getDesName() {
        return $this->des_name;
    }

    /**
     * Set des_order
     *
     * @param integer $desOrder
     * @return destination
     */
    public function setDesOrder($desOrder) {
        $this->des_order = $desOrder;

        return $this;
    }

    /**
     * Get des_order
     *
     * @return integer 
     */
    public function getDesOrder() {
        return $this->des_order;
    }

    /**
     * Set des_active
     *
     * @param boolean $desActive
     * @return destination
     */
    public function setDesActive($desActive) {
        $this->des_active = $desActive;
        return $this;
    }

    /**
     * Get des_active
     *
     * @return boolean 
     */
    public function getDesActive() {
        return $this->des_active;
    }

    /**
     * Add destinationsLang
     *
     * @param \MyCp\mycpBundle\Entity\destinationLang $destinationsLang
     * @return destination
     */
    public function addDestinationsLang(\MyCp\mycpBundle\Entity\destinationLang $destinationsLang) {
        $this->destinationsLang[] = $destinationsLang;

        return $this;
    }

    /**
     * Remove destinationsLang
     *
     * @param \MyCp\mycpBundle\Entity\destinationLang $destinationsLang
     */
    public function removeDestinationsLang(\MyCp\mycpBundle\Entity\destinationLang $destinationsLang) {
        $this->destinationsLang->removeElement($destinationsLang);
    }

    /**
     * Get destinationsLang
     *
     * @return Collection 
     */
    public function getDestinationsLang() {
        return $this->destinationsLang;
    }
    
    public function getFirstDestDesc(){
        return $this->destinationsLang[0];
    }

    /**
     * Add destinationsMunicipality
     *
     * @param \MyCp\mycpBundle\Entity\municipality $destinationsMunicipality
     * @return destination
     */
    public function addDestinationsMunicipality(\MyCp\mycpBundle\Entity\municipality $destinationsMunicipality) {
        $this->destinationsMunicipality[] = $destinationsMunicipality;

        return $this;
    }

    /**
     * Remove destinationsMunicipality
     *
     * @param \MyCp\mycpBundle\Entity\municipality $destinationsMunicipality
     */
    public function removeDestinationsMunicipality(\MyCp\mycpBundle\Entity\municipality $destinationsMunicipality) {
        $this->destinationsMunicipality->removeElement($destinationsMunicipality);
    }

    /**
     * Get destinationsMunicipality
     *
     * @return Collection 
     */
    public function getDestinationsMunicipality() {
        return $this->destinationsMunicipality;
    }

    /**
     * Add destinationsPhoto
     *
     * @param \MyCp\mycpBundle\Entity\photo $destinationsPhoto
     * @return destination
     */
    public function addDestinationsPhoto(\MyCp\mycpBundle\Entity\photo $destinationsPhoto) {
        $this->destinationsPhoto[] = $destinationsPhoto;

        return $this;
    }

    /**
     * Remove destinationsPhoto
     *
     * @param \MyCp\mycpBundle\Entity\photo $destinationsPhoto
     */
    public function removeDestinationsPhoto(\MyCp\mycpBundle\Entity\photo $destinationsPhoto) {
        $this->destinationsPhoto->removeElement($destinationsPhoto);
    }

    /**
     * Get destinationsPhoto
     *
     * @return Collection 
     */
    public function getDestinationsPhoto() {
        return $this->destinationsPhoto;
    }

    public function getFirstDestinationPhotoName() {
        $photo_name = $this->destinationsPhoto[0]->getPhoName();
        return file_exists(realpath("uploads/destinationImages/" . $photo_name)) ? $photo_name : "no_photo.png";
    }

}