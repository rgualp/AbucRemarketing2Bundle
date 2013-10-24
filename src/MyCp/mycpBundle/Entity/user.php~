<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * user
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\userRepository")
 */
class user implements UserInterface,  \Serializable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $user_id;

    /**
     * @var string
     *
     * @ORM\Column(name="user_name", type="string", length=255)
     */
    private $user_name;

    /**
     * @var string
     *
     * @ORM\Column(name="user_user_name", type="string", length=255)
     */
    private $user_user_name;

    /**
     * @var string
     *
     * @ORM\Column(name="user_last_name", type="string", length=255)
     */
    private $user_last_name;

    /**
     * @var string
     *
     * @ORM\Column(name="user_email", type="string", length=255)
     */
    private $user_email;

    /**
     * @var string
     *
     * @ORM\Column(name="user_address", type="string", length=255)
     */
    private $user_address;

    /**
     * @var string
     *
     * @ORM\Column(name="user_city", type="string", length=255)
     */
    private $user_city;

    /**
     * @var boolean
     *
     * @ORM\Column(name="user_enabled", type="boolean", nullable=true)
     */
    private $user_enabled;

    /**
     * @var boolean
     *
     * @ORM\Column(name="user_newsletters", type="boolean", nullable=true)
     */
    private $user_newsletters;

    /**
     * @ORM\ManyToOne(targetEntity="country",inversedBy="userCountry")
     * @ORM\JoinColumn(name="user_country",referencedColumnName="co_id")
     */
    private $user_country;

    /**
     * @var string
     *
     * @ORM\Column(name="user_phone", type="string", length=255)
     */
    private $user_phone;


    /**
     * @var string
     *
     * @ORM\Column(name="user_role", type="string", length=255)
     */
    private $user_role;

    /**
     * @ORM\ManyToOne(targetEntity="role",inversedBy="")
     * @ORM\JoinColumn(name="user_subrole",referencedColumnName="role_id")
     */
    private $user_subrole;

    /**
     * @var string
     *
     * @ORM\Column(name="user_password", type="string", length=255)
     */
    private $user_password;

    /**
     * @ORM\ManyToOne(targetEntity="photo",inversedBy="")
     * @ORM\JoinColumn(name="user_photo",referencedColumnName="pho_id")
     */
    private $user_photo;


    public function getSalt(){
        return '222';
    }

    public function unserialize($data)
    {
        $this->user_id = unserialize($data);
    }

    public function eraseCredentials(){

    }

    public function getRoles() {
        return array($this->user_role);
    }

    public function serialize()
    {
        return serialize($this->user_id);
    }

    public function getPassword()
    {
        return $this->user_password;
    }

    public function getUserName()
    {
        return $this->user_name;
    }




    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set user_name
     *
     * @param string $userName
     * @return user
     */
    public function setUserName($userName)
    {
        $this->user_name = $userName;
    
        return $this;
    }

    /**
     * Set user_user_name
     *
     * @param string $userUserName
     * @return user
     */
    public function setUserUserName($userUserName)
    {
        $this->user_user_name = $userUserName;
    
        return $this;
    }

    /**
     * Get user_user_name
     *
     * @return string 
     */
    public function getUserUserName()
    {
        return $this->user_user_name;
    }

    /**
     * Set user_last_name
     *
     * @param string $userLastName
     * @return user
     */
    public function setUserLastName($userLastName)
    {
        $this->user_last_name = $userLastName;
    
        return $this;
    }

    /**
     * Get user_last_name
     *
     * @return string 
     */
    public function getUserLastName()
    {
        return $this->user_last_name;
    }

    /**
     * Set user_email
     *
     * @param string $userEmail
     * @return user
     */
    public function setUserEmail($userEmail)
    {
        $this->user_email = $userEmail;
    
        return $this;
    }

    /**
     * Get user_email
     *
     * @return string 
     */
    public function getUserEmail()
    {
        return $this->user_email;
    }

    /**
     * Set user_phone
     *
     * @param string $userPhone
     * @return user
     */
    public function setUserPhone($userPhone)
    {
        $this->user_phone = $userPhone;
    
        return $this;
    }

    /**
     * Get user_phone
     *
     * @return string 
     */
    public function getUserPhone()
    {
        return $this->user_phone;
    }

    /**
     * Set user_role
     *
     * @param string $userRole
     * @return user
     */
    public function setUserRole($userRole)
    {
        $this->user_role = $userRole;

        return $this;
    }

    /**
     * Get user_role
     *
     * @return string 
     */
    public function getUserRole()
    {
        return $this->user_role;
    }

    /**
     * Set user_password
     *
     * @param string $userPassword
     * @return user
     */
    public function setUserPassword($userPassword)
    {
        $this->user_password = $userPassword;
    
        return $this;
    }

    /**
     * Get user_password
     *
     * @return string 
     */
    public function getUserPassword()
    {
        return $this->user_password;
    }


    /**
     * Set user_country
     *
     * @param \MyCp\mycpBundle\Entity\country $userCountry
     * @return user
     */
    public function setUserCountry(\MyCp\mycpBundle\Entity\country $userCountry = null)
    {
        $this->user_country = $userCountry;
    
        return $this;
    }

    /**
     * Get user_country
     *
     * @return \MyCp\mycpBundle\Entity\country 
     */
    public function getUserCountry()
    {
        return $this->user_country;
    }



    /**
     * Set user_address
     *
     * @param string $userAddress
     * @return user
     */
    public function setUserAddress($userAddress)
    {
        $this->user_address = $userAddress;
    
        return $this;
    }

    /**
     * Get user_address
     *
     * @return string 
     */
    public function getUserAddress()
    {
        return $this->user_address;
    }

    /**
     * Set user_city
     *
     * @param string $userCity
     * @return user
     */
    public function setUserCity($userCity)
    {
        $this->user_city = $userCity;
    
        return $this;
    }

    /**
     * Get user_city
     *
     * @return string 
     */
    public function getUserCity()
    {
        return $this->user_city;
    }

    /**
     * Set user_subrole
     *
     * @param \MyCp\mycpBundle\Entity\role $userSubrole
     * @return user
     */
    public function setUserSubrole(\MyCp\mycpBundle\Entity\role $userSubrole = null)
    {
        $this->user_subrole = $userSubrole;
    
        return $this;
    }

    /**
     * Get user_subrole
     *
     * @return \MyCp\mycpBundle\Entity\role 
     */
    public function getUserSubrole()
    {
        return $this->user_subrole;
    }

    /**
     * Set user_photo
     *
     * @param \MyCp\mycpBundle\Entity\photo $userPhoto
     * @return user
     */
    public function setUserPhoto(\MyCp\mycpBundle\Entity\photo $userPhoto = null)
    {
        $this->user_photo = $userPhoto;
    
        return $this;
    }

    /**
     * Get user_photo
     *
     * @return \MyCp\mycpBundle\Entity\photo 
     */
    public function getUserPhoto()
    {
        return $this->user_photo;
    }

    /**
     * Set user_enabled
     *
     * @param boolean $userEnabled
     * @return user
     */
    public function setUserEnabled($userEnabled)
    {
        $this->user_enabled = $userEnabled;
    
        return $this;
    }

    /**
     * Get user_enabled
     *
     * @return boolean 
     */
    public function getUserEnabled()
    {
        return $this->user_enabled;
    }

    /**
     * Set user_newsletters
     *
     * @param boolean $userNewsletters
     * @return user
     */
    public function setUserNewsletters($userNewsletters)
    {
        $this->user_newsletters = $userNewsletters;
    
        return $this;
    }

    /**
     * Get user_newsletters
     *
     * @return boolean 
     */
    public function getUserNewsletters()
    {
        return $this->user_newsletters;
    }
    
    public function getUserCompleteName(){
        return $this->user_user_name. ' '.$this->user_last_name;
    }

    public function __toString()
    {
        return $this->user_name;
    }

    
}