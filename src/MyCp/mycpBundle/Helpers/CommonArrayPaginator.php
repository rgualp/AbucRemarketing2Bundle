<?php

namespace MyCp\mycpBundle\Helpers;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Paginator form array collection
 *
 * @author laptop
 */
class CommonArrayPaginator extends CommonPaginator
{

    //current displayed page
    protected $currentpage;
    //limit items on one page
    protected $limit;
    //total number of pages that will be generated
    protected $numpages;
    //total items loaded from database
    protected $itemscount;
    //starting item number to be shown on page
    protected $offset;
    private $info;

    public function count()
    {
        return count($this->getQuery()->getResult());
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getIterator()
    {

        return $this->run()->getResult();
    }
}

?>
