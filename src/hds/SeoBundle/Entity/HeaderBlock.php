<?php

namespace hds\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * HeaderBlock
 *
 * @ORM\Table(name="hds_seo_headerblock")
 * @ORM\Entity(repositoryClass="hds\SeoBundle\Repository\HeaderBlock")
 */
class HeaderBlock
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
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Assert\NotBlank()
	 */
	protected $name;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $decription;

	/**
	 * @ORM\OneToMany(targetEntity="hds\SeoBundle\Entity\Header", mappedBy="header_block")
	 */
	private $headers;

	public function __toString(){
		return $this->name;
	}

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->headers = new \Doctrine\Common\Collections\ArrayCollection();
		$this->isActive= true;
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return HeaderBlock
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set decription
     *
     * @param string $decription
     *
     * @return HeaderBlock
     */
    public function setDecription($decription)
    {
        $this->decription = $decription;

        return $this;
    }

    /**
     * Get decription
     *
     * @return string
     */
    public function getDecription()
    {
        return $this->decription;
    }

    /**
     * Add header
     *
     * @param \hds\SeoBundle\Entity\Header $header
     *
     * @return HeaderBlock
     */
    public function addHeader(\hds\SeoBundle\Entity\Header $header)
    {
        $this->headers[] = $header;

        return $this;
    }

    /**
     * Remove header
     *
     * @param \hds\SeoBundle\Entity\Header $header
     */
    public function removeHeader(\hds\SeoBundle\Entity\Header $header)
    {
        $this->headers->removeElement($header);
    }

    /**
     * Get headers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
