<?php

namespace hds\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Header
 *
 * @ORM\Table(name="hds_seo_header")
 * @ORM\Entity(repositoryClass="hds\SeoBundle\Repository\Header")
 */
class Header
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
	 * @ORM\Column(type="string", columnDefinition="enum('meta', 'link')")
	 * @Assert\NotBlank()
	 */
	private $type_tag;

	/**
	 * @ORM\Column(type="text")
	 * @Assert\NotBlank()
	 */
	protected $tag;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $content;

	/**
	 * @ORM\OneToMany(targetEntity="hds\SeoBundle\Entity\BlockContent", mappedBy="header")
	 */
	private $contents;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $decription;

	/**
	 * @ORM\ManyToOne(targetEntity="hds\SeoBundle\Entity\HeaderBlock", inversedBy="headers", fetch="EXTRA_LAZY")
	 */
	private $header_block;


	public function __construct(){
	}

	public function __toString(){
		return $this->tag;
	}

	public function getFieldRewrite(){
		if($this->getTypeTag()=='link')
			return 'href';
		if($this->getTypeTag()=='meta')
			return 'content';
		return '';
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
	 * Set typeTag
	 *
	 * @param string $typeTag
	 *
	 * @return Header
	 */
	public function setTypeTag($typeTag)
	{
		$this->type_tag = $typeTag;

		return $this;
	}

	/**
	 * Get typeTag
	 *
	 * @return string
	 */
	public function getTypeTag()
	{
		return $this->type_tag;
	}

	/**
	 * Set tag
	 *
	 * @param string $tag
	 *
	 * @return Header
	 */
	public function setTag($tag)
	{
		$this->tag = $tag;

		return $this;
	}

	/**
	 * Get tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return $this->tag;
	}

	/**
	 * Set content
	 *
	 * @param string $content
	 *
	 * @return Header
	 */
	public function setContent($content)
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * Get content
	 *
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Set decription
	 *
	 * @param string $decription
	 *
	 * @return Header
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
	 * Set headerBlock
	 *
	 * @param \hds\SeoBundle\Entity\HeaderBlock $headerBlock
	 *
	 * @return Header
	 */
	public function setHeaderBlock(\hds\SeoBundle\Entity\HeaderBlock $headerBlock = null)
	{
		$this->header_block = $headerBlock;

		return $this;
	}

	/**
	 * Get headerBlock
	 *
	 * @return \hds\SeoBundle\Entity\HeaderBlock
	 */
	public function getHeaderBlock()
	{
		return $this->header_block;
	}

    /**
     * Add content
     *
     * @param \hds\SeoBundle\Entity\Header $content
     *
     * @return Header
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
