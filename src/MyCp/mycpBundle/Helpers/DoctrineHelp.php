<?php

namespace MyCp\mycpBundle\Helpers;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DoctrineHelp
 *
 * @author laptop
 */
class DoctrineHelp
{

    //put your code here

    static public function paginate($query, $currentPage = 1, $pageSize = 10)
    {
        $paginator = new CommonPaginator($query, $currentPage, $pageSize);
        $paginator->run();
        return $paginator;
    }

    static public function paginateScalar($query, $currentPage = 1, $pageSize = 10)
    {
        return new CommonArrayPaginator($query, $currentPage, $pageSize);

    }

}

?>
