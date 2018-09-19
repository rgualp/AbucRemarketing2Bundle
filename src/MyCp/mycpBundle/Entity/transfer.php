<?php
/**
 * Created by PhpStorm.
 * User: DragonStone
 * Date: 17/09/2018
 * Time: 10:01
 */

namespace MyCp\mycpBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
/**
 * transfer
 *
 * @ORM\Table(name="transfers")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\transferRepository")
 */
class transfer
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
     * @ORM\Column(name="transfer_from", type="string", length=255)
     */
    private $From;

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
    public function getFrom()
    {
        return $this->From;
    }
    public function transfer_from(){
        return $this->From;
    }

    /**
     * @param string $From
     */
    public function setFrom($From)
    {
        $this->From = $From;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->To;
    }

    /**
     * @param string $To
     */
    public function setTo($To)
    {
        $this->To = $To;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="transfer_to", type="string", length=255)
     */
    private $To;

    /**
     * @var float
     *
     * @ORM\Column(name="transfer_price", type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(name="transfer_price_van", type="decimal", precision=10, scale=2)
     */
    private $price_van;

    /**
     * @return float
     */
    public function getPriceVan()
    {
        return $this->price_van;
    }

    /**
     * @param float $price_van
     */
    public function setPriceVan($price_van)
    {
        $this->price_van = $price_van;
    }



}