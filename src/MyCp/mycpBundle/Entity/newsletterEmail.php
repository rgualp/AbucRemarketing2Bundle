<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * newsletterEmail
 *
 * @ORM\Table(name="newsletter_email")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\newsletterEmailRepository")
 */
class newsletterEmail
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
     * @ORM\ManyToOne(targetEntity="newsletter",inversedBy="emails")
     * @ORM\JoinColumn(name="newsletter",referencedColumnName="id")
     */
    private $newsletter;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="newsletterEmails")
     * @ORM\JoinColumn(name="language",referencedColumnName="lang_id")
     */
    private $language;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * @return mixed
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * @param mixed $newsletter
     * @return mixed
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     * @return mixed
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }



}
