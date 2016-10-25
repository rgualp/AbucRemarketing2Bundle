<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * newsletterContent
 *
 * @ORM\Table(name="newsletter_content")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\newsletterContentRepository")
 */
class newsletterContent
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
     * @ORM\ManyToOne(targetEntity="newsletter",inversedBy="contents")
     * @ORM\JoinColumn(name="newsletter",referencedColumnName="id")
     */
    private $newsletter;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="newsletterContents")
     * @ORM\JoinColumn(name="language",referencedColumnName="lang_id")
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="emailBody", type="text")
     */
    private $emailBody;


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
    public function getEmailBody()
    {
        return $this->emailBody;
    }

    /**
     * @param string $emailBody
     * @return mixed
     */
    public function setEmailBody($emailBody)
    {
        $this->emailBody = $emailBody;
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


}
