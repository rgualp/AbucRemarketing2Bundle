<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * destinationCategoryLangRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class destinationCategoryLangRepository extends EntityRepository
{

    function getCategories($type=null)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT dcl,cat FROM mycpBundle:destinationcategorylang dcl
        JOIN dcl.des_cat_id_cat cat
        GROUP BY dcl.des_cat_id_cat");
        if($type=='object')
            return $query->getResult();
        else
            return $query->getArrayResult();
    }


    function getForMap($locale)
    {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder()
            ->select("desCatLang", "cat")
            ->from("mycpBundle:destinationCategoryLang", "desCatLang")
            ->join("desCatLang.des_cat_id_cat", "cat")
            ->join("desCatLang.des_cat_id_lang", "lang")
            ->where("lang.lang_code = :langCode")
            ->setParameter("langCode", $locale);

        return $query->getQuery()->getResult();
    }
}
