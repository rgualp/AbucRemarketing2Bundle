<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * role
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\roleRepository")
 */
class role
{
    /**
     * @var integer
     *
     * @ORM\Column(name="role_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $role_id;

    /**
     * @var string
     *
     * @ORM\Column(name="role_name", type="string", length=255)
     */
    private $role_name;

    /**
     * @var integer
     *
     * @ORM\Column(name="role_parent", type="integer", nullable=true)
     */
    private $role_parent;

    /**
     * @var boolean
     *
     * @ORM\Column(name="role_fixed", type="boolean")
     */
    private $role_fixed;

    /**
     * @ORM\OneToMany(targetEntity="rolePermission", mappedBy="rp_role", cascade={"persist", "remove"})
     *
     */
    private $permissions;

    /**
     * Get role_id
     *
     * @return integer 
     */
    public function getRoleId()
    {
        return $this->role_id;
    }

    /**
     * Set role_name
     *
     * @param string $roleName
     * @return role
     */
    public function setRoleName($roleName)
    {
        $this->role_name = $roleName;
    
        return $this;
    }

    /**
     * Get role_name
     *
     * @return string 
     */
    public function getRoleName()
    {
        return $this->role_name;
    }

    /**
     * Set role_parent
     *
     * @param integer $roleParent
     * @return role
     */
    public function setRoleParent($roleParent)
    {
        $this->role_parent = $roleParent;
    
        return $this;
    }

    /**
     * Get role_parent
     *
     * @return integer 
     */
    public function getRoleParent()
    {
        return $this->role_parent;
    }


    /**
     * Set role_fixed
     *
     * @param boolean $roleFixed
     * @return role
     */
    public function setRoleFixed($roleFixed)
    {
        $this->role_fixed = $roleFixed;
    
        return $this;
    }

    /**
     * Get role_fixed
     *
     * @return boolean 
     */
    public function getRoleFixed()
    {
        return $this->role_fixed;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add permissions
     *
     * @param \MyCp\mycpBundle\Entity\rolePermission $permissions
     * @return role
     */
    public function addPermission(\MyCp\mycpBundle\Entity\rolePermission $permissions)
    {
        $this->permissions[] = $permissions;

        return $this;
    }

    /**
     * Remove permissions
     *
     * @param \MyCp\mycpBundle\Entity\rolePermission $permissions
     */
    public function removePermission(\MyCp\mycpBundle\Entity\rolePermission $permissions)
    {
        $this->permissions->removeElement($permissions);
    }

    /**
     * Get permissions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setPermissions(ArrayCollection $perm)
    {
         $this->permissions = $perm;
        return $this;
    }

    public function __toString(){
        return $this->role_name;
    }

    /*Logs functions*/
    public function getLogDescription()
    {
        return "Rol ".$this->getRoleName();
    }
}
