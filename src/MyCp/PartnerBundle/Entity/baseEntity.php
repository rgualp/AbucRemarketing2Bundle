<?php

namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/*
 *
 */
class baseEntity {

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\user", inversedBy="")
     * @ORM\JoinColumn(name="id_created_by", referencedColumnName="user_id", nullable=true)
     */
    protected $createdBy;

    /**
     * @var datetime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\user", inversedBy="")
     * @ORM\JoinColumn(name="id_modified_by", referencedColumnName="user_id", nullable=true)
     */
    protected $modifiedBy;

    /**
     * @var datetime
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    protected $modified;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->created = new \DateTime();
        $this->modified = new \DateTime();
    }


    /**
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param datetime $created
     * @return mixed
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $user
     * @return mixed
     */
    public function setCreatedBy($user)
    {
        $this->createdBy = $user;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param datetime $modified
     * @return mixed
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * @param mixed $modifiedBy
     * @return mixed
     */
    public function setModifiedBy($modifiedBy)
    {
        $this->modifiedBy = $modifiedBy;
        return $this;
    }

}
