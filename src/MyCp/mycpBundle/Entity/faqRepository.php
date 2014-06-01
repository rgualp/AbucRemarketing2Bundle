<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * faqRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class faqRepository extends EntityRepository
{
    function insert_faq($data)
    {
        $active=0;
        if(isset($data['public']))
            $active=1;
        $em = $this->getEntityManager();
        $faq=new faq();
        $category=$em->getRepository('mycpBundle:faqcategory')->find($data['category']);
        $faq->setFaqOrder(0);
        $faq->setFaqCategory($category);
        $faq->setFaqActive($active);
        $em->persist($faq);
        $keys=array_keys($data);

        foreach($keys as $item)
        {
            if(strpos($item, 'question')!==false)
            {

                $id=substr($item, 9, strlen($item));
                $faq_lang= new faqLang();
                $faq_lang->setFaqLangQuestion($data['question_'.$id]);
                $faq_lang->setFaqLangAnswer($data['answer_'.$id]);
                $repo=$em->getRepository('mycpBundle:lang');
                $lang=$repo->find($id);
                $faq_lang->setFaqLangLang($lang);
                $faq_lang->setFaqLangFaq($faq);
                $em->persist($faq_lang);
            }
        }
        $em->flush();
    }

    function edit_faq($data)
    {
        $id_faq=$data['edit_faq'];
        $active=0;
        if(isset($data['public']))
            $active=1;
        $em = $this->getEntityManager();
        $faq=$em->getRepository('mycpBundle:faq')->find($id_faq);
        $category=$em->getRepository('mycpBundle:faqcategory')->find($data['category']);
        $faq->setFaqOrder(0);
        $faq->setFaqActive($active);
        $faq->setFaqCategory($category);
        $em->persist($faq);


        $query = $em->createQuery("DELETE mycpBundle:faqLang fl WHERE fl.faq_lang_faq=$id_faq");
        $query->execute();

        $keys=array_keys($data);

        foreach($keys as $item)
        {
            if(strpos($item, 'question')!==false)
            {

                $id=substr($item, 9, strlen($item));
                $faq_lang= new faqLang();
                $faq_lang->setFaqLangQuestion($data['question_'.$id]);
                $faq_lang->setFaqLangAnswer($data['answer_'.$id]);
                $repo=$em->getRepository('mycpBundle:lang');
                $lang=$repo->find($id);
                $faq_lang->setFaqLangLang($lang);
                $faq_lang->setFaqLangFaq($faq);
                $em->persist($faq_lang);
            }
        }
        $em->flush();

    }

    function get_all_faqs($filter_name,$filter_active,$filter_category,$sort_by)
    {
        $string='';
        if($filter_active!='null' && $filter_active!='')
        {
            $string="AND f.faq_active = :filter_active";
        }

        $string2='';
        if($filter_category!='null' && $filter_category!='')
        {
            $string2="AND f.faq_category = :filter_category";
        }

        $string3='';
        if($sort_by=='null')
        {
            $string3='f.faq_order ASC';
        }
        else
        {
            $string3="fl.faq_lang_question $sort_by";
        }

        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT fl,f FROM mycpBundle:faqLang fl
        JOIN fl.faq_lang_faq f
        WHERE fl.faq_lang_question LIKE :filter_name $string
        $string2
        GROUP BY f.faq_id
        ORDER BY $string3");
        
        $query->setParameter('filter_name', "%".$filter_name."%");
        
        if($filter_active!='null' && $filter_active!='')
            $query->setParameter ('filter_active', $filter_active);
        
        if($filter_category!='null' && $filter_category!='')
            $query->setParameter ('filter_category', $filter_category);
        
        return $query->getResult();
    }

    /*     * *
    * Codigo Yanet
    */

    function get_faq_category_list($lang_code) {
        $em = $this->getEntityManager();
        $query_string = "SELECT catLang FROM mycpBundle:faqCategoryLang catLang
                        JOIN catLang.faq_cat_id_cat category
                        JOIN catLang.faq_cat_id_lang lang
                        WHERE lang.lang_code = :lang_code";

        return $em->createQuery($query_string)->setParameter('lang_code', $lang_code)->getResult();
    }

    function get_faq_list_by_category($lang_code, $category_id = null) {
        $em = $this->getEntityManager();
        $query_string = "SELECT faqLang FROM mycpBundle:faqLang faqLang
                        JOIN faqLang.faq_lang_faq faq
                        JOIN faqLang.faq_lang_lang lang
                        WHERE lang.lang_code = :lang_code";
        if ($category_id != null && $category_id != "")
            $query_string .= " AND faq.faq_category = :category_id";

        $query_string .= " ORDER BY faq.faq_order ASC ";

        $query = $em->createQuery($query_string)->setParameter('lang_code', $lang_code);
        
        if ($category_id != null && $category_id != "")
            $query->setParameter ('category_id', $category_id);
        
         return $query->getResult();
    }

    /*     * *
     * Fin - Codigo Yanet
     */

}
