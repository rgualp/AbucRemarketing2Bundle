<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * emailDestination
 *
 * @ORM\Table(name="email_destination")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\emailDestinationRepository")
 *
 */
class emailDestination
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
     * @ORM\Column(name="content_es", type="text")
     */
    private $contentEs;

    /**
     * @var string
     *
     * @ORM\Column(name="content_en", type="text")
     */
    private $contentEn;

    /**
     * @var string
     *
     * @ORM\Column(name="content_de", type="text")
     */
    private $contentDe;


    /**
     * @ORM\ManyToOne(targetEntity="destination")
     * @ORM\JoinColumn(name="destination", referencedColumnName="des_id", nullable=true)
     */
    private $destination;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content_es
     *
     * @param string $contentEs
     * @return emailDestination
     */
    public function setContentEs($contentEs)
    {
        $this->contentEs = $contentEs;
        return $this;
    }

    /**
     * Get content_es
     *
     * @return string
     */
    public function getContentEs()
    {
        return $this->contentEs;
    }

    /**
     * Set content_en
     *
     * @param string $contentEn
     * @return emailDestination
     */
    public function setContentEn($contentEn)
    {
        $this->contentEn = $contentEn;
        return $this;
    }

    /**
     * Get content_en
     *
     * @return string
     */
    public function getContentEn()
    {
        return $this->contentEn;
    }

    /**
     * Set content_de
     *
     * @param string $contentDe
     * @return emailDestination
     */
    public function setContentDe($contentDe)
    {
        $this->contentDe = $contentDe;
        return $this;
    }

    /**
     * Get content_de
     *
     * @return string
     */
    public function getContentDe()
    {
        return $this->contentDe;
    }

    /**
     * Get destination
     *
     * @return destination
     */
    public function getDestination() {
        return $this->destination;
    }

    /**
     * Set destination
     *
     * @param destination $destination
     * @return ownership
     */
    public function setDestination($destination) {
        $this->destination = $destination;
        return $this;
    }
}