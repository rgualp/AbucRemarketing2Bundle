<?php
    namespace MyCp\frontEndBundle\Twig\Extension;

    class utilsExtension extends \Twig_Extension
    {
        public function getName(){
            return "round";
        }

        public function getFunctions()
        {
            return array(
                'ceil_round' => new \Twig_Function_Method($this, 'ceil_round')
            );
        }

        public function ceil_round($number)
        {
            return ceil($number);
        }
    }