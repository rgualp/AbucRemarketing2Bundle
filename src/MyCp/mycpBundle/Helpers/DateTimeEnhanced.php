<?php

/**
 * Description of Utils
 *
 * @author Ing.Livan Capote Valdes
 */

namespace MyCp\mycpBundle\Helpers;

class DateTimeEnhanced extends \DateTime {

    public function returnAdd(\DateInterval $interval)
    {
        $dt = clone $this;
        $dt->add($interval);
        return $dt;
    }

    public function returnSub(\DateInterval $interval)
    {
        $dt = clone $this;
        $dt->sub($interval);
        return $dt;
    }

}

?>
