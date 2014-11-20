<?php

namespace MyCp\FrontEndBundle\Twig\Extension;

use MyCp\FrontEndBundle\Helpers\Utils;

class utilsExtension extends \Twig_Extension {

    private $session;
    private $entity_manager;

    public function __construct($session, $entity_manager) {
        $this->session = $session;
        $this->entity_manager = $entity_manager;
    }

    public function getName() {
        return "round";
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
            new \Twig_SimpleFilter('urlNormalize', array($this, 'urlNormalize')),
            new \Twig_SimpleFilter('statusName', array($this, 'statusName')),
        );
    }

    public function getFunctions() {
        return array(
            'ceil_round' => new \Twig_Function_Method($this, 'ceil_round'),
            'default_currency' => new \Twig_Function_Method($this, 'default_currency'),
            'price_in_currency' => new \Twig_Function_Method($this, 'price_in_currency'),
            'roomPriceBySeason' => new \Twig_Function_Method($this, 'roomPriceBySeason'),
            'reservationPriceBySeason' => new \Twig_Function_Method($this, 'reservationPriceBySeason'),
        );
    }

    public function ceil_round($number) {
        //$number=29;
        //$rate = ($this->session->get('curr_rate') == null ? 1 : $this->session->get('curr_rate'));
        //$number = $number*$rate;
        $number_array = explode('.', $number);
        $number_integer = $number_array[0];
        $number_decimal = 0;
        if (isset($number_array[1])) {
            $number_decimal = $number_array[1];
            if (strlen($number_decimal) > 2) {
                if ($number_decimal[2] > 0) {
                    $number_decimal[1] = $number_decimal[1] + 1;
                    $number_decimal = substr($number_decimal, 0, 2);
                }
            }
            return ($number_integer . '.' . $number_decimal);
        }

        return (number_format($number_integer, 2));
    }

    public function default_currency() {
        return $this->entity_manager->getRepository('mycpBundle:currency')->findOneBy(array('curr_default' => true));
    }

    public function price_in_currency() {
        return $this->entity_manager->getRepository('mycpBundle:currency')->findOneBy(array('curr_site_price_in' => true));
    }

    public function priceFilter($number, $currency = null, $decimals = 2, $decPoint = '.', $thousandsSep = ',') {
        $rate = 0;
        $symbol = "";

        if($currency != null)
        {
            $rate = $currency->getCurrCucChange();
            $symbol = $currency->getCurrSymbol();
        }
        else if ($this->session->get('curr_rate') == null || $this->session->get('curr_symbol') == null) {
            $price_in_currency = $this->entity_manager->getRepository('mycpBundle:currency')->findOneBy(array('curr_site_price_in' => true));
            $rate = $price_in_currency->getCurrCucChange();
            $symbol = $price_in_currency->getCurrSymbol();
        }
        else
        {
            $rate = $this->session->get('curr_rate');
            $symbol = $this->session->get('curr_symbol');
        }

        $price = number_format($number * $rate, $decimals, $decPoint, $thousandsSep);
        $price = $symbol . ' ' . $price;

        return $price;
    }

    public function urlNormalize($text) {
        return Utils::urlNormalize($text);
    }

    public function statusName($status_id) {
        return \MyCp\mycpBundle\Entity\ownershipStatus::statusName($status_id);
    }

    public function roomPriceBySeason($room, $seasonType)
    {
        return $room->getPriceBySeasonType($seasonType);
    }

    public function reservationPriceBySeason($reservation, $seasonType)
    {
        return \MyCp\FrontEndBundle\Helpers\ReservationHelper::reservationPriceBySeason($reservation, $seasonType);
    }

}
