<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipData
 *
 * @ORM\Table(name="ownershipdata")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\ownershipDataRepository")
 *
 */
class ownershipData
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
     * @ORM\OneToOne(targetEntity="ownership",inversedBy="data")
     * @ORM\JoinColumn(name="accommodation",referencedColumnName="own_id")
     */
    private $accommodation;

    /**
     * @var int
     *
     * @ORM\Column(name="activeRooms", type="integer", nullable=true)
     */
    private $activeRooms;

    /**
     * @var int
     *
     * @ORM\Column(name="publishedComments", type="integer", nullable=true)
     */
    private $publishedComments;

    /**
     * @var int
     *
     * @ORM\Column(name="reservedRooms", type="integer", nullable=true)
     */
    private $reservedRooms;

    /**
     * @var int
     *
     * @ORM\Column(name="photosCount", type="integer", nullable=true)
     */
    private $photosCount;

    /**
     * @ORM\OneToOne(targetEntity="ownershipPhoto",inversedBy="data")
     * @ORM\JoinColumn(name="principalPhoto",referencedColumnName="own_pho_id", nullable=true)
     */
    private $principalPhoto;

    /**
     * @var int
     *
     * @ORM\Column(name="insertedInCasaModule", type="boolean", nullable=true)
     */
    private $insertedInCasaModule;

    /**
     * @return mixed
     */
    public function getAccommodation()
    {
        return $this->accommodation;
    }

    /**
     * @param mixed $accommodation
     * @return mixed
     */
    public function setAccommodation($accommodation)
    {
        $this->accommodation = $accommodation;
        return $this;
    }

    /**
     * @return int
     */
    public function getActiveRooms()
    {
        return $this->activeRooms;
    }

    /**
     * @param int $activeRooms
     * @return mixed
     */
    public function setActiveRooms($activeRooms)
    {
        $this->activeRooms = $activeRooms;
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
     * @return mixed
     */
    public function getPrincipalPhoto()
    {
        return $this->principalPhoto;
    }

    /**
     * @param mixed $principalPhoto
     * @return mixed
     */
    public function setPrincipalPhoto($principalPhoto)
    {
        $this->principalPhoto = $principalPhoto;
        return $this;
    }

    /**
     * @return int
     */
    public function getPublishedComments()
    {
        return $this->publishedComments;
    }

    /**
     * @param int $publishedComments
     * @return mixed
     */
    public function setPublishedComments($publishedComments)
    {
        $this->publishedComments = $publishedComments;
        return $this;
    }

    /**
     * @return int
     */
    public function getReservedRooms()
    {
        return $this->reservedRooms;
    }

    /**
     * @param int $reservedRooms
     * @return mixed
     */
    public function setReservedRooms($reservedRooms)
    {
        $this->reservedRooms = $reservedRooms;
        return $this;
    }

    /**
     * @return int
     */
    public function getPhotosCount()
    {
        return $this->photosCount;
    }

    /**
     * @param int $photosCount
     * @return mixed
     */
    public function setPhotosCount($photosCount)
    {
        $this->photosCount = $photosCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getInsertedInCasaModule()
    {
        return $this->insertedInCasaModule;
    }

    /**
     * @param int $insertedInCasaModule
     * @return mixed
     */
    public function setInsertedInCasaModule($insertedInCasaModule)
    {
        $this->insertedInCasaModule = $insertedInCasaModule;
        return $this;
    }
}