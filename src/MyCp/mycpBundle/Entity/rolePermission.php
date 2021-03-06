<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * rolepermission
 *
 * @ORM\Table(name="rolepermission")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\rolePermissionRepository")
 */
class rolePermission
{
    /**
     * @var integer
     *
     * @ORM\Column(name="rp_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $rp_id;

    /**
     * @ORM\ManyToOne(targetEntity="role",inversedBy="permissions")
     * @ORM\JoinColumn(name="rp_role",referencedColumnName="role_id")
     */
    private $rp_role;

    /**
     * @ORM\ManyToOne(targetEntity="permission",inversedBy="")
     * @ORM\JoinColumn(name="rp_permission",referencedColumnName="perm_id")
     */
    private $rp_permission;

    /**
     * Get rp_id
     *
     * @return integer 
     */
    public function getRpId()
    {
        return $this->rp_id;
    }

    /**
     * Set rp_role
     *
     * @param \MyCp\mycpBundle\Entity\role $rpRole
     * @return rolePermission
     */
    public function setRpRole(\MyCp\mycpBundle\Entity\role $rpRole = null)
    {
        $this->rp_role = $rpRole;
    
        return $this;
    }

    /**
     * Get rp_role
     *
     * @return \MyCp\mycpBundle\Entity\role 
     */
    public function getRpRole()
    {
        return $this->rp_role;
    }

    /**
     * Set rp_permission
     *
     * @param \MyCp\mycpBundle\Entity\permission $rpPermission
     * @return rolePermission
     */
    public function setRpPermission(\MyCp\mycpBundle\Entity\permission $rpPermission = null)
    {
        $this->rp_permission = $rpPermission;
    
        return $this;
    }

    /**
     * Get rp_permission
     *
     * @return \MyCp\mycpBundle\Entity\permission 
     */
    public function getRpPermission()
    {
        return $this->rp_permission;
    }
    public function __toString(){
        return $this->getRpPermission()->getPermDescription();
    }

    /*Logs functions*/
    public function getLogDescription()
    {
        return "Rol ".$this->getRpRole()->getRoleName()." - Permiso ".$this->getRpPermission()->getPermDescription();
    }
}
