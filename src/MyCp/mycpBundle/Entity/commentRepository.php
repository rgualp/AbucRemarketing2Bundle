<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\comment;


/**
 * commentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class commentRepository extends EntityRepository
{
    function insert_comment($data, $user)
    {
         $em = $this->getEntityManager();
         
         $ownership = $em->getRepository('mycpBundle:ownership')->find($data['com_ownership_id']);
         
         $comment = new comment();
         $comment->setComDate(new \DateTime());
         $comment->setComOwnership($ownership);
         $comment->setComRate($data['com_rating']);
         $comment->setComComments($data['com_comments']);
         $comment->setComUser($user);
         $comment->setComPublic(true);

         $em->persist($comment);
         $em->flush();
         
         $newRating = ($ownership->getOwnRating() + $comment->getComRate()) / 2;
         $ownership->setOwnRating($newRating);
         
         if($comment->getComRate() >= 3)
         {
             $total_comments = $ownership->getOwnCommentsTotal() + 1;
             $ownership->setOwnCommentsTotal($total_comments);
         }
         $em->persist($ownership);
         $em->flush();
    }
    
    function get_comments($ownsership_id)
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT c FROM mycpBundle:comment c
                         WHERE c.com_public=1
                           AND c.com_ownership = :ownership_id
                         ORDER BY c.com_date DESC";

        return $em->createQuery($query_string)->setParameter('ownership_id', $ownsership_id)->getResult();
    }

    function get_all_comment($filter_ownership,$filter_user,$filter_keyword, $filter_rate,$sort_by)
    {
        $string='';
        if($filter_user!='null' && $filter_user!='')
        {
            $string="AND c.com_user = :filter_user";
        }

        $string2='';
        if($filter_keyword!='null' && $filter_keyword!='')
        {
            $string2="AND c.com_comments LIKE :filter_keyword";
        }
        $string3='';
        if($filter_rate!='null' && $filter_rate!='')
        {
            $string3="AND c.com_rate = :filter_rate";
        }


        $string4='';
        switch($sort_by){
            case 0:
                $string4="ORDER BY own.own_mcp_code ASC";
                break;

            case 1:
                $string4="ORDER BY own.own_mcp_code DESC";
                break;


        }
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT c,own,us FROM mycpBundle:comment c
        JOIN c.com_ownership own JOIN c.com_user us WHERE own.own_mcp_code LIKE :filter_ownership $string $string2 $string3 $string4 ");
        
        if($filter_user!='null' && $filter_user!='')
            $query->setParameter('filter_user', $filter_user);
        
        if($filter_keyword!='null' && $filter_keyword!='')
            $query->setParameter('filter_keyword', "%".$filter_keyword."%");
        
        if($filter_rate!='null' && $filter_rate!='')
            $query->setParameter('filter_rate', $filter_rate);
        
        $query->setParameter('filter_ownership', "%".$filter_ownership."%");
        
        return $query->getResult();
    }
    
    function can_comment($user, $own_id)
    {

        if ($user != null && $user != "anon.")
        {
            $em = $this->getEntityManager();
            $query_string = "SELECT own_r FROM mycpBundle:ownershipReservation own_r JOIN own_r.own_res_gen_res_id gen_res
                             WHERE gen_res.gen_res_own_id = :own_id".
                             " AND gen_res.gen_res_user_id = :user_id".
                             " AND own_r.own_res_status = 5";
            $result=$em->createQuery($query_string)->setParameters(array('own_id' => $own_id, 'user_id' => $user))->getResult();
            //var_dump($result); exit();
            return count($result) > 0;
        }
        return false;
    }
    
    function get_user_comments($id_user)
    {
        if($id_user != null)
        {
            $em = $this->getEntityManager();
            
            $query_string = "SELECT com FROM mycpBundle:comment com 
                             WHERE com.com_user = :id_user 
                             AND com.com_ownership IS NOT NULL 
                             AND com.com_public = 1
                             ORDER BY com.com_date DESC";
            
            return $em->createQuery($query_string)->setParameter('id_user', $id_user)->getResult();
        }
    }
}
