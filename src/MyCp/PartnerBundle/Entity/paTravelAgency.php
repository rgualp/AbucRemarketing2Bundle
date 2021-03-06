<?php


namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * paTravelAgency
 *
 * @ORM\Table(name="pa_travel_agency")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paTravelAgencyRepository")
 * @ORM\EntityListeners({"MyCp\PartnerBundle\Listener\AgencyListener"})
 *
 */
class paTravelAgency extends baseEntity
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="phoneAux", type="string", length=255, nullable=true)
     */
    private $phoneAux;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=500)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=50, nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\ManyToOne(targetEntity="\MyCp\mycpBundle\Entity\country",inversedBy="travelAgencies")
     * @ORM\JoinColumn(name="country",referencedColumnName="co_id")
     */
    private $country;

    /**
     * @ORM\OneToMany(targetEntity="paContact", mappedBy="travelAgency",cascade={"persist", "remove"})
     */
    private $contacts;

    /**
     * @ORM\OneToMany(targetEntity="paAgencyPackage", mappedBy="travelAgency")
     */
    private $agencyPackages;

    /**
     * @ORM\OneToMany(targetEntity="paClient", mappedBy="travelAgency")
     */
    private $clients;

    /**
     * @ORM\OneToMany(targetEntity="paTourOperator", mappedBy="travelAgency")
     */
    private $tourOperators;

    /**
     * @var decimal
     *
     * @ORM\Column(name="commission", type="decimal", precision=2)
     */
    private $commission;
    /**
     *
     * @ORM\OneToOne(targetEntity="MyCp\PartnerBundle\Entity\paAccount")
     * @ORM\JoinColumn(name="account", referencedColumnName="account_id")
     */
    private $account;
    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\photo",inversedBy="")
     * @ORM\JoinColumn(name="agency_logo",referencedColumnName="pho_id")
     */
    private $agency_logo;

    /**
     * Set agency_logo
     *
     * @param \MyCp\mycpBundle\Entity\photo $agencyPhoto
     * @return paTravelAgency
     */
    public function setUserPhoto(\MyCp\mycpBundle\Entity\photo $agencyPhoto = null)
    {
        $this->agency_logo = $agencyPhoto;

        return $this;
    }

    /**
     * Get agency_logo
     *
     * @return \MyCp\mycpBundle\Entity\photo
     */
    public function getUserPhoto()
    {
        return $this->agency_logo;
    }
    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    public function __construct() {
        parent::__construct();

        $this->contacts = new ArrayCollection();
        $this->agencyPackages = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->tourOperators = new ArrayCollection();
        $this->commission = 10;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return mixed
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     * @return mixed
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return mixed
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }
    /**
     * @return string
     */
    public function getPhoneAux()
    {
        return $this->phoneAux;
    }

    /**
     * @param string $phoneAux
     * @return mixed
     */
    public function setPhoneAux($phoneAux)
    {
        $this->phoneAux = $phoneAux;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param mixed $contacts
     * @return mixed
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;
        return $this;
    }


    /**
     * Add contact
     *
     * @param paContact $contact
     *
     * @return mixed
     */
    public function addContact(paContact $contact)
    {
        $contact->setTravelAgency($this);
        $this->contacts[] = $contact;

        return $this;
    }

    /**
     * Remove contact
     *
     * @param paContact $contact
     */
    public function removeContact(paContact $contact)
    {
        $this->contacts->removeElement($contact);
    }

    /**
 * @return mixed
 */
    public function getAgencyPackages()
    {
        return $this->agencyPackages;
    }

    /**
     * @param mixed $agencyPackages
     * @return mixed
     */
    public function setAgencyPackages($agencyPackages)
    {
        $this->agencyPackages = $agencyPackages;
        return $this;
    }

    /**
     * Add agencyPackage
     *
     * @param paAgencyPackage $agencyPackage
     *
     * @return mixed
     */
    public function addAgencyPackage(paAgencyPackage $agencyPackage)
    {
        $this->agencyPackages[] = $agencyPackage;

        return $this;
    }

    /**
     * Remove agencyPackage
     *
     * @param paAgencyPackage $agencyPackage
     */
    public function removeAgencyPackage(paAgencyPackage $agencyPackage)
    {
        $this->agencyPackages->removeElement($agencyPackage);
    }

    /**
     * @return mixed
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * @param mixed $clients
     * @return mixed
     */
    public function setClients($clients)
    {
        $this->clients = $clients;
        return $this;
    }

    /**
     * Add client
     *
     * @param paClient $client
     *
     * @return mixed
     */
    public function addClient(paClient $client)
    {
        $this->clients[] = $client;

        return $this;
    }

    /**
     * Remove client
     *
     * @param paClient $client
     */
    public function removeClient(paClient $client)
    {
        $this->clients->removeElement($client);
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     * @return mixed
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
        return $this;
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
     * @return decimal
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * @param decimal $commission
     * @return mixed
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;
        return $this;
    }


}
