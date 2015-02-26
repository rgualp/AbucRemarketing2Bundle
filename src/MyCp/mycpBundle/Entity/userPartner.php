<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * userpartner
 *
 * @ORM\Table(name="userpartner")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\userPartnerRepository")
 */
class userPartner
{
    /**
     * @var integer
     *
     * @ORM\Column(name="user_partner_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $user_partner_id;

    /**
     * @var string
     *
     * @ORM\Column(name="user_partner_company_code", type="string", length=255)
     */
    private $user_partner_company_code;

    /**
     * @var string
     *
     * @ORM\Column(name="user_partner_company_name", type="string", length=255)
     */
    private $user_partner_company_name;

    /**
     * @ORM\ManyToOne(targetEntity="currency",inversedBy="")
     * @ORM\JoinColumn(name="user_partner_currency",referencedColumnName="curr_id")
     */
    private $user_partner_currency;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="")
     * @ORM\JoinColumn(name="user_partner_language",referencedColumnName="lang_id")
     */
    private $user_partner_language;

    /**
     * @ORM\ManyToOne(targetEntity="user",inversedBy="")
     * @ORM\JoinColumn(name="user_partner_user",referencedColumnName="user_id")
     */
    private $user_partner_user;

    /**
     * @var string
     *
     * @ORM\Column(name="user_partner_contact_person", type="string", length=255)
     */
    private $user_partner_contact_person;




    /**
     * Get user_partner_id
     *
     * @return integer
     */
    public function getUserPartnerId()
    {
        return $this->user_partner_id;
    }

    /**
     * Set user_partner_company_code
     *
     * @param string $userPartnerCompanyCode
     * @return userPartner
     */
    public function setUserPartnerCompanyCode($userPartnerCompanyCode)
    {
        $this->user_partner_company_code = $userPartnerCompanyCode;

        return $this;
    }

    /**
     * Get user_partner_company_code
     *
     * @return string
     */
    public function getUserPartnerCompanyCode()
    {
        return $this->user_partner_company_code;
    }

    /**
     * Set user_partner_company_name
     *
     * @param string $userPartnerCompanyName
     * @return userPartner
     */
    public function setUserPartnerCompanyName($userPartnerCompanyName)
    {
        $this->user_partner_company_name = $userPartnerCompanyName;

        return $this;
    }

    /**
     * Get user_partner_company_name
     *
     * @return string
     */
    public function getUserPartnerCompanyName()
    {
        return $this->user_partner_company_name;
    }

    /**
     * Set user_partner_contact_person
     *
     * @param string $userPartnerContactPerson
     * @return userPartner
     */
    public function setUserPartnerContactPerson($userPartnerContactPerson)
    {
        $this->user_partner_contact_person = $userPartnerContactPerson;

        return $this;
    }

    /**
     * Get user_partner_contact_person
     *
     * @return string
     */
    public function getUserPartnerContactPerson()
    {
        return $this->user_partner_contact_person;
    }

    /**
     * Set user_partner_currency
     *
     * @param \MyCp\mycpBundle\Entity\currency $userPartnerCurrency
     * @return userPartner
     */
    public function setUserPartnerCurrency(\MyCp\mycpBundle\Entity\currency $userPartnerCurrency = null)
    {
        $this->user_partner_currency = $userPartnerCurrency;

        return $this;
    }

    /**
     * Get user_partner_currency
     *
     * @return \MyCp\mycpBundle\Entity\currency
     */
    public function getUserPartnerCurrency()
    {
        return $this->user_partner_currency;
    }

    /**
     * Set user_partner_language
     *
     * @param \MyCp\mycpBundle\Entity\lang $userPartnerLanguage
     * @return userPartner
     */
    public function setUserPartnerLanguage(\MyCp\mycpBundle\Entity\lang $userPartnerLanguage = null)
    {
        $this->user_partner_language = $userPartnerLanguage;

        return $this;
    }

    /**
     * Get user_partner_language
     *
     * @return \MyCp\mycpBundle\Entity\lang
     */
    public function getUserPartnerLanguage()
    {
        return $this->user_partner_language;
    }

    /**
     * Set user_partner_user
     *
     * @param \MyCp\mycpBundle\Entity\user $userPartnerUser
     * @return userPartner
     */
    public function setUserPartnerUser(\MyCp\mycpBundle\Entity\user $userPartnerUser = null)
    {
        $this->user_partner_user = $userPartnerUser;

        return $this;
    }

    /**
     * Get user_partner_user
     *
     * @return \MyCp\mycpBundle\Entity\user
     */
    public function getUserPartnerUser()
    {
        return $this->user_partner_user;
    }
}