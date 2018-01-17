<?php

namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * paContact
 *
 * @ORM\Table(name="pa_invoice")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paInvoiceRepository")
 *
 *
 */
class paInvoice{

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
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $filename;

    /**
     * @return DateTime
     */
    public function getInvoicedate()
    {
        return $this->invoicedate;
    }

    /**
     * @param DateTime $invoicedate
     */
    public function setInvoicedate($invoicedate)
    {
        $this->invoicedate = $invoicedate;
    }

    /**
     * @var DateTime
     *
     * @ORM\Column(name="invoicedate", type="date")
     */
    private $invoicedate;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }







}
