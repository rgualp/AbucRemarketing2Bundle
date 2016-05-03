<?php

namespace hds\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * BlockContent
 *
 * @ORM\Table(name="hds_seo_blockcontent")
 * @ORM\Entity(repositoryClass="hds\SeoBundle\Repository\BlockContent")
 */
class BlockContent
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
	 * @ORM\ManyToOne(targetEntity="hds\SeoBundle\Entity\Block", inversedBy="contents", fetch="EXTRA_LAZY")
	 */
	private $block;

	/**
	 * @ORM\ManyToOne(targetEntity="hds\SeoBundle\Entity\Header", inversedBy="", fetch="EXTRA_LAZY")
	 */
	private $header;

	/**
	 * @ORM\Column(type="string", length=25, nullable=true)
	 * @Assert\NotBlank()
	 */
	protected $language_code;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $content;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $decription;


	public function __construct(){
	}

	public function __toString(){
		return $this->id.'';
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
     * Set content
     *
     * @param string $content
     *
     * @return BlockContent
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
     * @return BlockContent
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
     * Set block
     *
     * @param \hds\SeoBundle\Entity\Block $block
     *
     * @return BlockContent
     */
    public function setBlock(\hds\SeoBundle\Entity\Block $block = null)
    {
        $this->block = $block;

        return $this;
    }

    /**
     * Get block
     *
     * @return \hds\SeoBundle\Entity\Block
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * Set header
     *
     * @param \hds\SeoBundle\Entity\Header $header
     *
     * @return BlockContent
     */
    public function setHeader(\hds\SeoBundle\Entity\Header $header = null)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get header
     *
     * @return \hds\SeoBundle\Entity\Header
     */
    public function getHeader()
    {
        return $this->header;
    }

	public function getMeta(array $replacements=array()){

		$tag_str= $this->getHeader()->getTag();
		$type_tag_str= $this->getHeader()->getTypeTag();
		$field_rewrite_str= $this->getHeader()->getFieldRewrite();
		$content= $this->getContent();
		foreach($replacements as $key=>$replacement){
			$content= str_replace($key, $replacement, $content);
		}

		$doc = new \DOMDocument();
		$doc->loadHTML($tag_str);

		$tag_element = $doc->getElementsByTagName($type_tag_str)->item(0);
		$content_to_replace =  $tag_element->getAttribute($field_rewrite_str);
		$tag_meta_content= str_replace($content_to_replace, $content, $tag_str);
		return $tag_meta_content;
	}

    /**
     * Set languageCode
     *
     * @param string $languageCode
     *
     * @return BlockContent
     */
    public function setLanguageCode($languageCode)
    {
        $this->language_code = $languageCode;

        return $this;
    }

    /**
     * Get languageCode
     *
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->language_code;
    }
}
