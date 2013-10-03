<?php
namespace MyCp\frontEndBundle\Helpers;


class Time
{
    public static function dates_between($startdate, $enddate, $format=null){

        (is_int($startdate)) ? 1 : $startdate = strtotime($startdate);
        (is_int($enddate)) ? 1 : $enddate = strtotime($enddate);

        if($startdate > $enddate){
            return false; //The end date is before start date
        }

        while($startdate < $enddate){
            $arr[] = ($format) ? date($format, $startdate) : $startdate;
            $startdate += 86400;
        }
        $arr[] = ($format) ? date($format, $enddate) : $enddate;

        return $arr;
    }

    public function season_by_date($date)
    {
        $temp=strtotime('2000-'.date('m', $date).'-'.date('d', $date));

        //top season
        $top_from=strtotime('2000-07-15');
        $top_to=strtotime('2000-08-31');

        $top_from_2=strtotime('2000-12-16');
        $top_to_2=strtotime('2000-03-15');

        if($top_from <= $temp and $top_to >= $temp OR $top_from_2 <= $temp and $top_to_2 >= $temp)
        {
            return 'top';
        }
        else
        {
            return 'down';
        }
    }

}