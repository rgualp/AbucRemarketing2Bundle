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
            //$number=29;
            $number_array= explode('.', $number);
            $number_integer=$number_array[0];
            $number_decimal=0;
            if(isset($number_array[1]))
            {
                $number_decimal=$number_array[1];
                if(strlen($number_decimal)>2)
                {
                    if($number_decimal[2]>0)
                    {
                        $number_decimal[1]=$number_decimal[1]+1;
                        $number_decimal=substr($number_decimal,0,2);
                    }
                }
                return ($number_integer.'.'.$number_decimal);
            }

            return ($number_integer);
        }
    }