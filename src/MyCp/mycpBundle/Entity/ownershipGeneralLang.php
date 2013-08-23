<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipGeneralLang
 *
 * @ORM\Table(name="ownershipgenerallang")
 * @ORM\Entity
 */
class ownershipGeneralLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ogl_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $ogl_id;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="langslang")
     * @ORM\JoinColumn(name="ogl_id_lang",referencedColumnName="lang_id")
     */
    private $ogl_id_lang;

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="ownershipGeneralLang")
     * @ORM\JoinColumn(name="ogl_id_ownership",referencedColumnName="own_id")
     */
    private $ogl_ownership;

   

    /**
     * Get ogl_id
     *
     * @return integer 
     */
    public function getOglId()
    {
        return $this->ogl_id;
    }

    /**
     * Set ogl_id_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $oglIdLang
     * @return ownershipGeneralLang
     */
    public function setOglIdLang(\MyCp\mycpBundle\Entity\lang $oglIdLang = null)
    {
        $this->ogl_id_lang = $oglIdLang;
    
        return $this;
    }

    /**
     * Get ogl_id_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getOglIdLang()
    {
        return $this->ogl_id_lang;
    }

    /**
     * Set ogl_ownership
     *
     * @param \MyCp\mycpBundle\Entity\ownership $oglOwnership
     * @return ownershipGeneralLang
     */
    public function setOglOwnership(\MyCp\mycpBundle\Entity\ownership $oglOwnership = null)
    {
        $this->ogl_ownership = $oglOwnership;
    
        return $this;
    }

    /**
     * Get ogl_ownership
     *
     * @return \MyCp\mycpBundle\Entity\ownership 
     */
    public function getOglOwnership()
    {
        return $this->ogl_ownership;
    }
}