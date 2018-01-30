<?php

namespace MyCp\mycpBundle\Helpers;

use Doctrine\ORM\Tools\Pagination\Paginator;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Paginator form array collection
 *
 * @author laptop
 */
class CommonPaginator extends Paginator {

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

    public function __construct($query, $page = 1, $size = 10, $fetchJoinCollection = true) {
        $this->info = array();
        $this->currentpage = $page;
        $this->limit = $size;
        if ($size < 1)
            $this->limit = 10;

        if ($page < 1)
            $this->currentpage = 1;

        $this->offset = self::getOffsetValue($this->currentpage, $this->limit);  //$this->limit * ($this->currentpage - 1);
        parent::__construct($query, $fetchJoinCollection);
    }

    public static function getOffsetValue($page, $size) {
        return $size * ($page - 1);
    }

    public function getOneInfo($key) {
        return $this->info[$key];
    }

    public function getInfo() {
        return $this->info;
    }

    public function setInfo($info) {
        $this->info = $info;
    }

    public function addInfo($key, $value) {
        $this->info[$key] = $value;
    }

    public function getNumPages() {
        //If limit is set to 0 or set to number bigger then total items count
        //display all in one page
        $this->numpages = 1;
        if (($this->limit < 1) || ($this->limit > $this->count())) {
            $this->numpages = 1;
        } else {

            //Calculate rest numbers from dividing operation so we can add one
            //more page for this items
            $restItemsNum = $this->count() % $this->limit;
            //if rest items > 0 then add one more page else just divide items
            //by limit

            $restItemsNum > 0 ? $this->numpages = intval($this->count() / $this->limit) + 1 : $this->numpages = intval($this->count() / $this->limit);
        }
        return $this->numpages;
    }

    public function calculateOffset() {
        //Calculet offset for items based on current page number
        $this->offset = ($this->currentpage - 1) * $this->limit;
        return $this->offset;
    }

    //For using from controller
    public function getLimit() {
        return $this->limit;
    }

    //For using from controller
    public function getCurrentPage() {
        return $this->currentpage;
    }

    //For using from controller
    public function getOffset() {
        return $this->offset;
    }

    public function run() {
        return $this->getQuery()->setFirstResult($this->getOffset())->setMaxResults($this->limit);
    }

}

?>
