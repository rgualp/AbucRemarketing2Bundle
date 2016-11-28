<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * user
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\userRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class user implements AdvancedUserInterface,  \Serializable
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
     * @ORM\Column(name="user_address", type="string", length=255, nullable=true)
     */
    private $user_address;

    /**
     * @var string
     *
     * @ORM\Column(name="user_city", type="string", length=255, nullable=true)
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
     * @ORM\Column(name="user_created_by_migration", type="boolean", nullable=true)
     */
    private $user_created_by_migration;

    /**
     * @var boolean
     *
     * @ORM\Column(name="user_newsletters", type="boolean", nullable=true)
     */
    private $user_newsletters;

    /**
     * @ORM\ManyToOne(targetEntity="country",inversedBy="users")
     * @ORM\JoinColumn(name="user_country",referencedColumnName="co_id")
     */
    private $user_country;

    /**
     * @var string
     *
     * @ORM\Column(name="user_phone", type="string", length=255, nullable=true)
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
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean", nullable=true)
     */
    private $locked;

    /**
     * @ORM\ManyToOne(targetEntity="photo",inversedBy="")
     * @ORM\JoinColumn(name="user_photo",referencedColumnName="pho_id")
     */
    private $user_photo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="user_creation_date", type="datetime", nullable=true)
     */
    private $user_creation_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="user_activation_date", type="datetime", nullable=true)
     */
    private $user_activation_date;

    /**
     * @ORM\OneToMany(targetEntity="comment", mappedBy="com_user")
     */
    private $comments;
    /**
     * @ORM\OneToMany(targetEntity="userCasa", mappedBy="user_casa_user")
     */
   private $user_user_casa;

    /**
     * @ORM\ManyToOne(targetEntity="currency",inversedBy="")
     * @ORM\JoinColumn(name="user_currency",referencedColumnName="curr_id")
     */
    private $user_currency;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="")
     * @ORM\JoinColumn(name="user_language",referencedColumnName="lang_id")
     */
    private $user_language;

    /**
     * @ORM\OneToMany(targetEntity="MyCp\PartnerBundle\Entity\paTourOperator", mappedBy="tourOperator")
     */
    private $tourOperators;

    /**
     * @ORM\OneToMany(targetEntity="penalty", mappedBy="user")
     */
    private $createdPenalties;

    public function __construct() {
        $this->comments = new ArrayCollection();
        $this->modified_reservations = new ArrayCollection();
        $this->tourOperators = new ArrayCollection();
        $this->createdPenalties = new ArrayCollection();
    }



    public function getSalt(){
        return '222';
    }
    public function serialize()
    {
        return serialize(array(
            $this->user_id,
            $this->user_user_name,
            $this->user_email, //Add email
            $this->user_password,
            $this->user_enabled,
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->user_id,
            $this->user_user_name,
            $this->user_email, //Add email
            $this->user_password,
            $this->user_enabled,
            ) = unserialize($serialized);

    }
//    public function unserialize($data)
//    {
//        $this->user_id = unserialize($data);
//    }

    public function eraseCredentials(){

    }

    public function getRoles() {
        return array($this->user_role);
    }

//    public function serialize()
//    {
//        return serialize($this->user_id);
//    }

    public function getPassword()
    {
        return $this->user_password;
    }
    public function getUsername()
    {
        return $this->user_email;
    }

    public function getName()
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
     * Set user_created_by_migration
     *
     * @param boolean $userCreatedByMigration
     * @return user
     */
    public function setUserCreatedByMigration($userCreatedByMigration)
    {
        $this->user_created_by_migration = $userCreatedByMigration;

        return $this;
    }

    /**
     * Get user_created_by_migration
     *
     * @return boolean
     */
    public function getUserCreatedByMigration()
    {
        return $this->user_created_by_migration;
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

    /**
     * Set user_creation_date
     *
     * @param string $userCreationDate
     * @return user
     */
    public function setUserCreationDate($userCreationDate)
    {
        $this->user_creation_date = $userCreationDate;

        return $this;
    }

        /**
        * @ORM\PrePersist
        */
       public function setUserCreationValue()
       {
           $this->user_creation_date = new \DateTime();
       }

    /**
     * Get user_creation_date
     *
     * @return user_creation_date
     */
    public function getUserCreationDate()
    {
       return $this->user_creation_date;
    }

    /**
     * Set user_activation_date
     *
     * @param string $userActivationDate
     * @return user
     */
    public function setUserActivationDate($userActivationDate)
    {
        $this->user_activation_date = $userActivationDate;

        return $this;
    }

    /**
     * Get user_activation_date
     *
     * @return user_activation_date
     */
    public function getUserActivationDate()
    {
       return $this->user_activation_date;
    }

    public function getUserCompleteName(){
        return $this->user_user_name. ' '.$this->user_last_name;
    }

    public function __toString()
    {
        $country = ($this->getUserCountry() != null) ? " (".$this->getUserCountry()->getCoName(). ")" : "";
        return (($this->getUserCompleteName() != "") ? $this->getUserCompleteName() : $this->getUserUserName()).$country;
    }



    /**
     * Set locked
     *
     * @param boolean $locked
     *
     * @return user
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }
    public function isAccountNonLocked()
    {
        return !$this->locked;
    }
    public function isLocked()
    {
        return !$this->isAccountNonLocked();
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
       return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->user_enabled;
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     * @return mixed
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return mixed
     */
   /* public function getModifiedReservations()
    {
        return $this->modified_reservations;
    }*/

    /**
     * @param mixed $modified_reservations
     * @return mixed
     */
    /*public function setModifiedReservations($modified_reservations)
    {
        $this->modified_reservations = $modified_reservations;
        return $this;
    }*/

    /*Logs functions*/
    public function getLogDescription()
    {
        return "Usuario ".$this->getUserCompleteName();
    }


    /**
     * Add comment
     *
     * @param \MyCp\mycpBundle\Entity\comment $comment
     *
     * @return user
     */
    public function addComment(\MyCp\mycpBundle\Entity\comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \MyCp\mycpBundle\Entity\comment $comment
     */
    public function removeComment(\MyCp\mycpBundle\Entity\comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Add userUserCasa
     *
     * @param \MyCp\mycpBundle\Entity\userCasa $userUserCasa
     *
     * @return user
     */
    public function addUserUserCasa(\MyCp\mycpBundle\Entity\userCasa $userUserCasa)
    {
        $this->user_user_casa[] = $userUserCasa;

        return $this;
    }

    /**
     * Remove userUserCasa
     *
     * @param \MyCp\mycpBundle\Entity\userCasa $userUserCasa
     */
    public function removeUserUserCasa(\MyCp\mycpBundle\Entity\userCasa $userUserCasa)
    {
        $this->user_user_casa->removeElement($userUserCasa);
    }

    /**
     * Get userUserCasa
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserUserCasa()
    {
        return $this->user_user_casa;
    }

    /**
     * Set user_currency
     *
     * @param \MyCp\mycpBundle\Entity\currency $userCurrency
     * @return user
     */
    public function setUserCurrency(\MyCp\mycpBundle\Entity\currency $userCurrency = null)
    {
        $this->user_currency = $userCurrency;

        return $this;
    }

    /**
     * Get user_currency
     *
     * @return \MyCp\mycpBundle\Entity\currency
     */
    public function getUserCurrency()
    {
        return $this->user_currency;
    }

    /**
     * Set user_language
     *
     * @param \MyCp\mycpBundle\Entity\lang $userLanguage
     * @return user
     */
    public function setUserLanguage(\MyCp\mycpBundle\Entity\lang $userLanguage = null)
    {
        $this->user_language = $userLanguage;

        return $this;
    }

    /**
     * Get user_language
     *
     * @return \MyCp\mycpBundle\Entity\lang
     */
    public function getUserLanguage()
    {
        return $this->user_language;
    }

    /**
     * Add tourOperator
     *
     * @param \MyCp\PartnerBundle\Entity\paTourOperator $tourOperator
     *
     * @return paTravelAgency
     */
    public function addTourOperator(\MyCp\PartnerBundle\Entity\paTourOperator $tourOperator)
    {
        $this->tourOperators[] = $tourOperator;

        return $this;
    }

    /**
     * Remove tourOperator
     *
     * @param \MyCp\PartnerBundle\Entity\paTourOperator $tourOperator
     */
    public function removeTourOperator(\MyCp\PartnerBundle\Entity\paTourOperator $tourOperator)
    {
        $this->tourOperators->removeElement($tourOperator);
    }

    /**
     * Get tourOperators
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTourOperators()
    {
        return $this->tourOperators;
    }

    /**
     * Set tourOperators
     *
     * @return mixed
     */
    public function setTourOperators($tourOperators)
    {
        $this->tourOperators = $tourOperators;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getModifiedReservations()
    {
        return $this->modified_reservations;
    }

    /**
     * @param ArrayCollection $modified_reservations
     * @return mixed
     */
    public function setModifiedReservations($modified_reservations)
    {
        $this->modified_reservations = $modified_reservations;
        return $this;
    }

    /**
     * Add modified reservation
     *
     * @param mixed $reservation
     *
     * @return user
     */
    public function addModifiedReservation($reservation)
    {
        $this->modified_reservations[] = $reservation;
        return $this;
    }

    /**
     * Remove modified reservation
     *
     * @param mixed $reservation
     */
    public function removeModifiedReservation($reservation)
    {
        $this->modified_reservations->removeElement($reservation);
    }

    /**
     * @return mixed
     */
    public function getCreatedPenalties()
    {
        return $this->createdPenalties;
    }

    /**
     * @param mixed $createdPenalties
     * @return mixed
     */
    public function setCreatedPenalties($createdPenalties)
    {
        $this->createdPenalties = $createdPenalties;
        return $this;
    }

    /**
     * Add created penalty
     *
     * @param \MyCp\mycpBundle\Entity\penalty $penalty
     *
     * @return user
     */
    public function addCreatedPenalty(penalty $penalty)
    {
        $this->createdPenalties[] = $penalty;
        return $this;
    }

    /**
     * Remove created penalty
     *
     * @param \MyCp\mycpBundle\Entity\penalty $penalty
     */
    public function removeCreatedPenalty(penalty $penalty)
    {
        $this->createdPenalties->removeElement($penalty);
    }
}
