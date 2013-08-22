<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * albumCategory
 *
 * @ORM\Table(name="albumcategory")
 * @ORM\Entity
 */
class albumCategory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="alb_cat_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $alb_cat_id;

    /**
     * Get alb_cat_id
     *
     * @return integer 
     */
    public function getAlbCatId()
    {
        return $this->alb_cat_id;
    }
}