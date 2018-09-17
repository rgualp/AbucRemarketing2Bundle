<?php
/**
 * Created by PhpStorm.
 * User: DragonStone
 * Date: 17/09/2018
 * Time: 10:01
 */

namespace MyCp\mycpBundle\Entity;
use Doctrine\ORM\EntityRepository;

use Doctrine\ORM\Mapping as ORM;
/**
 * transfer
 *
 * @ORM\Table(name="transfers")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\transfer")
 */
class transferRepository extends EntityRepository
{

}