<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use MyCp\PartnerBundle\Entity\paTravelAgency;

/**
 * country
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\countryRepository")
 */
class country
{
    /**
     * @var integer
     *
     * @ORM\Column(name="co_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $co_id;

    /**
     * @var string
     *
     * @ORM\Column(name="co_code", type="string", length=255)
     */
    private $co_code;

    /**
     * @var string
     *
     * @ORM\Column(name="co_name", type="string", length=255)
     */
    private $co_name;
    
    /**
     * @ORM\OneToMany(targetEntity="user", mappedBy="user_country")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="MyCp\PartnerBundle\Entity\paTravelAgency", mappedBy="country")
     */
    private $travelAgencies;


    public function __construct() {
        $this->users = new ArrayCollection();
        $this->travelAgencies = new ArrayCollection();
    }
    
    public function getUsers(){
        return $this->users;
    }

    /**
     * @param mixed $users
     * @return mixed
     */
    public function setUsers($users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * Add user
     *
     * @param user $user
     * @return user
     */
    public function addUser(user $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     * @param user $user
     * @return $this
     */
    public function removeUser(user $user)
    {
        $this->users->removeElement($user);
        return $this;
    }


    
    /**
     * Get co_id
     *
     * @return integer 
     */
    public function getCoId()
    {
        return $this->co_id;
    }

    /**
     * Set co_code
     *
     * @param string $coCode
     * @return country
     */
    public function setCoCode($coCode)
    {
        $this->co_code = $coCode;
    
        return $this;
    }

    /**
     * Get co_code
     *
     * @return string 
     */
    public function getCoCode()
    {
        return $this->co_code;
    }

    /**
     * Set co_name
     *
     * @param string $coName
     * @return country
     */
    public function setCoName($coName)
    {
        $this->co_name = $coName;
    
        return $this;
    }

    /**
     * Get co_name
     *
     * @return string 
     */
    public function getCoName()
    {
        return $this->co_name;
    }

    public function __toString()
    {
        return $this->co_name;
    }

    /**
     * @return mixed
     */
    public function getTravelAgencies()
    {
        return $this->travelAgencies;
    }

    /**
 * @param mixed $travelAgencies
 * @return mixed
 */
    public function setTravelAgencies($travelAgencies)
    {
        $this->travelAgencies = $travelAgencies;
        return $this;
    }

    /**
     * Add travel agency
     *
     * @param travelAgency|paTravelAgency $travelAgency
     *
     * @return user
     */
    public function addTravelAgency(paTravelAgency $travelAgency)
    {
        $this->travelAgencies[] = $travelAgency;

        return $this;
    }

    /**
     * Remove travel agency
     * @param travelAgency|paTravelAgency $travelAgency
     * @return $this
     */
    public function removeTravelAgency(paTravelAgency $travelAgency)
    {
        $this->travelAgencies->removeElement($travelAgency);
        return $this;
    }
}