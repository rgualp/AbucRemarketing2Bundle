<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * userStaffManager
 *
 * @ORM\Table(name="userstaffmanager")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\userStaffManagerRepository")
 */
class userStaffManager
{
    /**
     * @var integer
     *
     * @ORM\Column(name="user_staff_manager_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $user_staff_manager_id;

    /**
     * @ORM\ManyToOne(targetEntity="user",inversedBy="")
     * @ORM\JoinColumn(name="user_staff_manager_user",referencedColumnName="user_id")
     */
    private $user_staff_manager_user;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="destination", inversedBy="")
     * @ORM\JoinTable(name="userstaffmanager_destination",
     *   joinColumns={
     *     @ORM\JoinColumn(name="user_staff_manager", referencedColumnName="user_staff_manager_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="destination", referencedColumnName="des_id")
     *   }
     * )
     */
    private $destinations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->destinations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get userStaffManagerId
     *
     * @return integer
     */
    public function getUserStaffManagerId()
    {
        return $this->user_staff_manager_id;
    }

    /**
     * Set userStaffManagerUser
     *
     * @param \MyCp\mycpBundle\Entity\user $userStaffManagerUser
     *
     * @return userStaffManager
     */
    public function setUserStaffManagerUser(\MyCp\mycpBundle\Entity\user $userStaffManagerUser = null)
    {
        $this->user_staff_manager_user = $userStaffManagerUser;

        return $this;
    }

    /**
     * Get userStaffManagerUser
     *
     * @return \MyCp\mycpBundle\Entity\user
     */
    public function getUserStaffManagerUser()
    {
        return $this->user_staff_manager_user;
    }

    /**
     * Add destination
     *
     * @param \MyCp\mycpBundle\Entity\destination $destination
     *
     * @return userStaffManager
     */
    public function addDestination(\MyCp\mycpBundle\Entity\destination $destination)
    {
        $this->destinations[] = $destination;

        return $this;
    }

    /**
     * Remove destination
     *
     * @param \MyCp\mycpBundle\Entity\destination $destination
     */
    public function removeDestination(\MyCp\mycpBundle\Entity\destination $destination)
    {
        $this->destinations->removeElement($destination);
    }

    /**
     * Get destinations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDestinations()
    {
        return $this->destinations;
    }
}
