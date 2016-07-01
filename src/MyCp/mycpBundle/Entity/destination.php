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
     * @ORM\JoinColumn(name="des_id", referencedColumnName="des_lang_des_id", nullable=true)
     */
    private $destinationsLang;

    /**
     * @ORM\OneToMany(targetEntity="destinationKeywordLang",mappedBy="dkl_destination")
     */
    private $des_keyword_langs;


    private $destinationsMunicipality;

    private $destinationsPhoto;

    /**
     * @var string
     *
     * @ORM\Column(name="des_poblation", type="integer", length=255)
     */
    private $des_poblation;

    /**
     * @var string
     *
     * @ORM\Column(name="des_ref_place", type="string", length=255)
     */
    private $des_ref_place;

    /**
     * @var string
     *
     * @ORM\Column(name="des_geolocate_x", type="string", length=255, nullable=true)
     */
    private $des_geolocate_x;

    /**
     * @var string
     *
     * @ORM\Column(name="des_geolocate_y", type="string", length=255, nullable=true)
     */
    private $des_geolocate_y;

    /**
     * @var integer
     *
     * @ORM\Column(name="des_cat_location_x", type="integer", nullable=true)
     */
    private $des_cat_location_x;

    /**
     * @var integer
     *
     * @ORM\Column(name="des_cat_location_y", type="integer", nullable=true)
     */
    private $des_cat_location_y;

    /**
     * @var integer
     *
     * @ORM\Column(name="des_cat_location_prov_x", type="integer", nullable=true)
     */
    private $des_cat_location_prov_x;

    /**
     * @var integer
     *
     * @ORM\Column(name="des_cat_location_prov_y", type="integer", nullable=true)
     */
    private $des_cat_location_prov_y;

    /**
    * @ORM\ManyToMany(targetEntity="destinationCategory")
    * @ORM\JoinTable(name="mmdestinationcategory",
    * joinColumns={@ORM\JoinColumn(name="cat_id_des" , referencedColumnName="des_id")},
    * inverseJoinColumns={@ORM\JoinColumn(name="cat_id_cat", referencedColumnName="des_cat_id")})
    */
    private $des_categories;

    /**
     * @ORM\OneToMany(targetEntity="destination",mappedBy="des_loc_destination")
     */
    private $locations;

    /**
     * Constructor
     */
    public function __construct() {
        $this->destinationsLang = new ArrayCollection();
        $this->des_categories = new ArrayCollection();
        $this->des_keyword_langs = new ArrayCollection();
        $this->locations = new ArrayCollection();
    }

    /**
     * Get des_id
     *
     * @return integer
     */
    public function getDesId() {
        return $this->des_id;
    }

    public function getDesKeywordLangs()
    {
        return $this->des_keyword_langs;
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDestinationsLang() {
        return $this->destinationsLang;
    }

    public function getFirstDestDesc() {
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
        if (count($this->destinationsPhoto) > 0) {
            $photo_name = $this->destinationsPhoto[0]->getPhoName();
            if (file_exists(realpath("uploads/destinationImages/$photo_name"))) {
                return $photo_name;
            }
        }
        return "no_photo.png";
    }

    public function __toString() {
        return $this->getDesName();
    }

    public function getDesPoblation() {
        return $this->des_poblation;
    }

    public function setDesPoblation($des_poblation) {
        $this->des_poblation = $des_poblation;
    }

    public function getDesRefPlace() {
        return $this->des_ref_place;
    }

    public function setDesRefPlace($des_ref_place) {
        $this->des_ref_place = $des_ref_place;
    }

    /**
     * Set des_geolocate_x
     *
     * @param string $value
     * @return destination
     */
    public function setDesGeolocateX($value) {
        $this->des_geolocate_x = $value;

        return $this;
    }

    /**
     * Get des_geolocate_x
     *
     * @return string
     */
    public function getDesGeolocateX() {
        return $this->des_geolocate_x;
    }

    /**
     * Set des_geolocate_y
     *
     * @param string $value
     * @return destination
     */
    public function setDesGeolocateY($value) {
        $this->des_geolocate_y = $value;

        return $this;
    }

    /**
     * Get des_geolocate_y
     *
     * @return string
     */
    public function getDesGeolocateY() {
        return $this->des_geolocate_y;
    }

    /**
     * Yanet - Fin
     */

    /**
     * Add des_categories
     *
     * @param \MyCp\mycpBundle\Entity\destinationCategory $desCategories
     * @return destination
     */
    public function addDesCategorie(\MyCp\mycpBundle\Entity\destinationCategory $desCategories)
    {
        $this->des_categories[] = $desCategories;

        return $this;
    }

    /**
     * Remove des_categories
     *
     * @param \MyCp\mycpBundle\Entity\destinationCategory $desCategories
     */
    public function removeDesCategorie(\MyCp\mycpBundle\Entity\destinationCategory $desCategories)
    {
        $this->des_categories->removeElement($desCategories);
    }

    /**
     * Get des_categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDesCategories()
    {
        return $this->des_categories;
    }

    /**
     * Set des_cat_location_x
     *
     * @param integer $desCatLocationX
     * @return destination
     */
    public function setDesCatLocationX($desCatLocationX)
    {
        $this->des_cat_location_x = $desCatLocationX;

        return $this;
    }

    /**
     * Get des_cat_location_x
     *
     * @return integer
     */
    public function getDesCatLocationX()
    {
        return $this->des_cat_location_x;
    }

    /**
     * Set des_cat_location_y
     *
     * @param integer $desCatLocationY
     * @return destination
     */
    public function setDesCatLocationY($desCatLocationY)
    {
        $this->des_cat_location_y = $desCatLocationY;

        return $this;
    }

    /**
     * Get des_cat_location_y
     *
     * @return integer
     */
    public function getDesCatLocationY()
    {
        return $this->des_cat_location_y;
    }

    /**
     * Set des_cat_location_prov_x
     *
     * @param integer $desCatLocationProvX
     * @return destination
     */
    public function setDesCatLocationProvX($desCatLocationProvX)
    {
        $this->des_cat_location_prov_x = $desCatLocationProvX;

        return $this;
    }

    /**
     * Get des_cat_location_prov_x
     *
     * @return integer
     */
    public function getDesCatLocationProvX()
    {
        return $this->des_cat_location_prov_x;
    }

    /**
     * Set des_cat_location_prov_y
     *
     * @param integer $desCatLocationProvY
     * @return destination
     */
    public function setDesCatLocationProvY($desCatLocationProvY)
    {
        $this->des_cat_location_prov_y = $desCatLocationProvY;

        return $this;
    }

    /**
     * Get des_cat_location_prov_y
     *
     * @return integer
     */
    public function getDesCatLocationProvY()
    {
        return $this->des_cat_location_prov_y;
    }

    /**
     * Add des_categories
     *
     * @param \MyCp\mycpBundle\Entity\destinationCategory $desCategories
     * @return destination
     */
    public function addDesCategory(\MyCp\mycpBundle\Entity\destinationCategory $desCategories)
    {
        $this->des_categories[] = $desCategories;

        return $this;
    }

    /**
     * Remove des_categories
     *
     * @param \MyCp\mycpBundle\Entity\destinationCategory $desCategories
     */
    public function removeDesCategory(\MyCp\mycpBundle\Entity\destinationCategory $desCategories)
    {
        $this->des_categories->removeElement($desCategories);
    }

    /*Logs functions*/
    public function getLogDescription()
    {
        return "Destino ".$this->getDesName();
    }

    /**
     * @return mixed
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @param mixed $locations
     */
    public function setLocations($locations)
    {
        $this->locations = $locations;
    }

}
