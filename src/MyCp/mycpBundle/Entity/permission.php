<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * permission
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\permissionRepository")
 */
class permission
{
    /**
     * @var integer
     *
     * @ORM\Column(name="perm_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $perm_id;

    /**
     * @var string
     *
     * @ORM\Column(name="perm_description", type="string", length=255)
     */
    private $perm_description;

    /**
     * @var string
     *
     * @ORM\Column(name="perm_category", type="string", length=255)
     */
    private $perm_category;


    /**
     * @var string
     *
     * @ORM\Column(name="perm_route", type="string", length=255,nullable=true)
     */
    private $perm_route;


    /**
     * Get perm_id
     *
     * @return integer 
     */
    public function getPermId()
    {
        return $this->perm_id;
    }

    /**
     * Set perm_description
     *
     * @param string $permDescription
     * @return permission
     */
    public function setPermDescription($permDescription)
    {
        $this->perm_description = $permDescription;
    
        return $this;
    }

    /**
     * Get perm_description
     *
     * @return string 
     */
    public function getPermDescription()
    {
        return $this->perm_description;
    }

    /**
     * Set perm_category
     *
     * @param string $permCategory
     * @return permission
     */
    public function setPermCategory($permCategory)
    {
        $this->perm_category = $permCategory;
    
        return $this;
    }

    /**
     * Get perm_category
     *
     * @return string 
     */
    public function getPermCategory()
    {
        return $this->perm_category;
    }

    /**
     * Set perm_route
     *
     * @param string $permRoute
     * @return permission
     */
    public function setPermRoute($permRoute)
    {
        $this->perm_route = $permRoute;
    
        return $this;
    }

    /**
     * Get perm_route
     *
     * @return string 
     */
    public function getPermRoute()
    {
        return $this->perm_route;
    }
    public function __toString(){
        return $this->getPermDescription();
    }

    /*Logs functions*/
    public function getLogDescription()
    {
        return "Permiso ".$this->getPermDescription();
    }
}