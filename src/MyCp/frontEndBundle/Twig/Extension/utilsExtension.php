<?php

namespace MyCp\frontEndBundle\Twig\Extension;

class utilsExtension extends \Twig_Extension {

    private $session;

    public function __construct($session) {
        $this->session = $session;
    }

    public function getName() {
        return "round";
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
        );
    }

    public function getFunctions() {
        return array(
            'ceil_round' => new \Twig_Function_Method($this, 'ceil_round')
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
       // $curr_symbol = ($this->session->get('curr_symbol') == null ? "CUC" : strtoupper($this->session->get('curr_symbol')));

        return (number_format($number_integer, 2));
    }

    public function priceFilter($number, $decimals = 2, $decPoint = '.', $thousandsSep = ',') {

        $rate = ($this->session->get('curr_rate') == null ? 1 : $this->session->get('curr_rate'));
        $price = number_format($number*$rate, $decimals, $decPoint, $thousandsSep);

        $curr_symbol = ($this->session->get('curr_symbol') == null ? "CUC" : $this->session->get('curr_symbol'));

        $price = $curr_symbol . ' ' . $price;

        return $price;
    }

}