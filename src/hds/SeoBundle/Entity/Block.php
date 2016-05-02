<?php

namespace hds\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Block
 *
 * @ORM\Table(name="hds_seo_block")
 * @ORM\Entity(repositoryClass="hds\SeoBundle\Repository\Block")
 */
class Block
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
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Assert\NotBlank()
	 */
	protected $location;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $isActive;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $decription;

	/**
	 * @ORM\OneToMany(targetEntity="hds\SeoBundle\Entity\BlockContent", mappedBy="block", cascade={"all"})
	 */
	private $contents;

	/**
	 * Constructor
	 */
	public function __construct()
	{
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
     * @return Block
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
     * Set location
     *
     * @param string $location
     *
     * @return Block
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Block
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set decription
     *
     * @param string $decription
     *
     * @return Block
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
     * Add content
     *
     * @param \hds\SeoBundle\Entity\Header $content
     *
     * @return Block
     */
    public function addContent(\hds\SeoBundle\Entity\Header $content)
    {
        $this->contents[] = $content;

        return $this;
    }

    /**
     * Remove content
     *
     * @param \hds\SeoBundle\Entity\Header $content
     */
    public function removeContent(\hds\SeoBundle\Entity\Header $content)
    {
        $this->contents->removeElement($content);
    }

    /**
     * Get contents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContents()
    {
        return $this->contents;
    }
}
