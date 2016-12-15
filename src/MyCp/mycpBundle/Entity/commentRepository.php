<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use MyCp\mycpBundle\Helpers\OrderByHelper;

/**
 * commentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class commentRepository extends EntityRepository {

    function insert($data, $user) {
        $em = $this->getEntityManager();

        $ownership = $em->getRepository('mycpBundle:ownership')->find($data['com_ownership_id']);
        $reservations = count($em->getRepository('mycpBundle:ownershipReservation')->getByOwnershipAndUser(ownershipReservation::STATUS_RESERVED, $ownership->getOwnId(), $user->getUserId()));
        $is_public = $reservations > 0;

        $comment = new comment();
        $comment->setComDate(new \DateTime());
        $comment->setComOwnership($ownership);
        $comment->setComRate($data['com_rating']);
        $comment->setComComments($data['com_comments']);
        $comment->setComUser($user);
        $comment->setComPublic($is_public);

        $em->persist($comment);
        $em->flush();

        if ($is_public) {
            $newRating = ($ownership->getOwnRating() + $comment->getComRate()) / 2;
            $ownership->setOwnRating($newRating);

            $newRanking = $em->getRepository("mycpBundle:ownership")->getRankingFormula($ownership);
            $ownership->setOwnRanking($newRanking);
        }

        if ($comment->getComRate() >= 3 && $is_public) {
            $total_comments = $ownership->getOwnCommentsTotal() + 1;
            $ownership->setOwnCommentsTotal($total_comments);
        }
        $em->persist($ownership);
        $em->flush();
    }

    function getByOwnership($ownsership_id, $just_public = 1) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("c.com_rate", "c.com_date", "c.com_comments", "u.user_user_name", "u.user_last_name")
            ->from("mycpBundle:comment", "c")
            ->join("c.com_user", "u")
            ->where("c.com_ownership = :idAccommodation")
            ->orderBy("c.com_date", "DESC")
            ->setParameter("idAccommodation", $ownsership_id);

        if($just_public)
            $qb->andWhere("c.com_public = 1");

        return $qb->getQuery();
    }

    function getRecommendations($ownsership_id) {
        $em = $this->getEntityManager();
        $query_string = "SELECT c
                         FROM mycpBundle:comment c
                         WHERE  c.com_ownership = :ownership_id AND c.com_public=1 AND c.com_rate >= 3";

        return $em->createQuery($query_string)->setParameter('ownership_id', $ownsership_id)->getResult();
    }

    function getAll($filter_ownership, $filter_user, $filter_keyword, $filter_rate, $sort_by, $filter_date_from, $filter_date_to) {
        $queryStr = "SELECT c,own,us,
        (SELECT COUNT(res) FROM mycpBundle:generalReservation res
                JOIN res.own_reservations r
                JOIN r.own_res_reservation_booking b
                JOIN b.payments p
                WHERE res.gen_res_user_id = us.user_id
                AND res.gen_res_own_id = own.own_id
                AND res.gen_res_status = ".\MyCp\mycpBundle\Entity\generalReservation::STATUS_RESERVED."
                AND (p.status = ".PaymentHelper::STATUS_SUCCESS." OR p.status = ".PaymentHelper::STATUS_PROCESSED."))
        FROM mycpBundle:comment c
        JOIN c.com_ownership own
        JOIN c.com_user us
        WHERE own.own_mcp_code
        LIKE :filter_ownership";
        return $this->getAllByQuery($filter_ownership, $filter_user, $filter_keyword, $filter_rate, $sort_by, -1, $queryStr, $filter_date_from, $filter_date_to);
    }

    function getLastAdded($filter_ownership, $filter_user, $filter_keyword, $filter_rate, $sort_by) {
        $queryStr = "SELECT c,own,us,
        (SELECT COUNT(res) FROM mycpBundle:generalReservation res
                JOIN res.own_reservations r
                JOIN r.own_res_reservation_booking b
                JOIN b.payments p
                WHERE res.gen_res_user_id = us.user_id
                AND res.gen_res_own_id = own.own_id
                AND res.gen_res_status = ".generalReservation::STATUS_RESERVED."
                AND (p.status = ".PaymentHelper::STATUS_SUCCESS." OR p.status = ".PaymentHelper::STATUS_PROCESSED."))
        FROM mycpBundle:comment c
        JOIN c.com_ownership own JOIN c.com_user us WHERE own.own_mcp_code LIKE :filter_ownership
        AND c.com_public = 0";
        return $this->getAllByQuery($filter_ownership, $filter_user, $filter_keyword, $filter_rate, 2, -1, $queryStr);
    }

    function getByUserCasa($filter_ownership, $filter_user, $filter_keyword, $filter_rate, $sort_by, $user_casa_id) {
        $queryStr = "SELECT c,own,us FROM mycpBundle:comment c
        JOIN c.com_ownership own
        JOIN c.com_user us
        JOIN mycpBundle:userCasa uca WITH own.own_id = uca.user_casa_ownership WHERE c.com_public = 1 and own.own_mcp_code LIKE :filter_ownership and uca.user_casa_id = :user_casa_id";
        return $this->getAllByQuery($filter_ownership, $filter_user, $filter_keyword, $filter_rate, $sort_by, $user_casa_id, $queryStr);
    }

    function getAllByQuery($filter_ownership, $filter_user, $filter_keyword, $filter_rate, $sort_by, $user_casa_id, $queryStr, $filter_date_from = "", $filter_date_to = "") {
        $string = '';
        if ($filter_user != 'null' && $filter_user != '') {
            $string = "AND c.com_user = :filter_user";
        }

        $string2 = '';
        if ($filter_keyword != 'null' && $filter_keyword != '') {
            $string2 = "AND c.com_comments LIKE :filter_keyword";
        }
        $string3 = '';
        if ($filter_rate != 'null' && $filter_rate != '') {
            $string3 = "AND c.com_rate = :filter_rate";
        }
        $stringDateFrom = '';
        if ($filter_date_from != 'null' && $filter_date_from != '') {
            $stringDateFrom = "AND c.com_date >= :filter_date_from";
        }
        $stringDateTo = '';
        if ($filter_date_to != 'null' && $filter_date_to != '') {
            $stringDateTo = "AND c.com_date <= :filter_date_to";
        }


        $string4 = '';
        switch ($sort_by) {
            case OrderByHelper::COMMENT_ACCOMMODATION_CODE_ASC:
                $string4 = "ORDER BY own.own_mcp_code ASC, c.com_date DESC";
                break;

            case OrderByHelper::COMMENT_ACCOMMODATION_CODE_DESC:
                $string4 = "ORDER BY own.own_mcp_code DESC, c.com_date DESC";
                break;
            case OrderByHelper::DEFAULT_ORDER_BY:
            case OrderByHelper::COMMENT_DATE:
                $string4 = "ORDER BY c.com_date DESC";
                break;
            case OrderByHelper::COMMENT_RATING:
                $string4 = "ORDER BY c.com_rate DESC, c.com_date DESC";
                break;
            case OrderByHelper::COMMENT_USER_NAME_ASC:
                $string4 = "ORDER BY us.user_name ASC, c.com_date DESC";
                break;
            case OrderByHelper::COMMENT_USER_NAME_DESC:
                $string4 = "ORDER BY us.user_name DESC, c.com_date DESC";
                break;
        }

        $queryStr = $queryStr . " " . $string . " " . $string2 . " " . $string3 . " " . $stringDateFrom . " " . $stringDateTo . " " . $string4;
        $em = $this->getEntityManager();
        $query = $em->createQuery($queryStr);

        if ($filter_user != 'null' && $filter_user != '')
            $query->setParameter('filter_user', $filter_user);

        if ($filter_keyword != 'null' && $filter_keyword != '')
            $query->setParameter('filter_keyword', "%" . $filter_keyword . "%");

        if ($filter_rate != 'null' && $filter_rate != '')
            $query->setParameter('filter_rate', $filter_rate);

        if ($filter_date_from != 'null' && $filter_date_from != '') {
            $query->setParameter('filter_date_from', $filter_date_from);
        }
        if ($filter_date_to != 'null' && $filter_date_to != '') {
            $query->setParameter('filter_date_to', $filter_date_to);
        }

        if ($user_casa_id != -1)
            $query->setParameter("user_casa_id", $user_casa_id);

        $query->setParameter('filter_ownership', "%" . $filter_ownership . "%");

        return $query->getResult();
    }

    function canComment($user, $own_id) {

        if ($user != null && $user != "anon.") {
            $em = $this->getEntityManager();
            $reservations = count($em->getRepository('mycpBundle:ownershipReservation')->getByOwnershipAndUser(ownershipReservation::STATUS_RESERVED, $own_id, $user));

            $query_string = "SELECT com FROM mycpBundle:comment com
                            WHERE com.com_ownership = :own_id
                              AND com.com_user = :user_id";
            $comments = count($em->createQuery($query_string)->setParameters(array('own_id' => $own_id, 'user_id' => $user))->getResult());
            return ($reservations > $comments) || ($reservations == 0 && $comments == 0);
        }
        return false;
    }

    function canPublicComment($user, $own_id) {
        if ($user != null && $user != "anon.") {
            $em = $this->getEntityManager();
            $reservations = count($em->getRepository('mycpBundle:ownershipReservation')->getByOwnershipAndUser(ownershipReservation::STATUS_RESERVED, $own_id, $user));
            return $reservations > 0;
        }
        return false;
    }

    function getByUser($id_user) {
        if ($id_user != null) {
            $em = $this->getEntityManager();

            $query_string = "SELECT com FROM mycpBundle:comment com
                             WHERE com.com_user = :id_user
                             AND com.com_ownership IS NOT NULL
                             AND com.com_public = 1
                             ORDER BY com.com_date DESC";

            return $em->createQuery($query_string)->setParameter('id_user', $id_user)->getResult();
        }
    }

    function getOldUnpublished($date) {
        $em = $this->getEntityManager();

        $query_string = "SELECT com FROM mycpBundle:comment com
                             WHERE com.com_public = 0
                               AND com.com_date >= :date
                             ORDER BY com.com_date DESC";

        return $em->createQuery($query_string)->setParameter('date', $date)->getResult();
    }

    public function publicMultiples($ids) {
        $em = $this->getEntityManager();
        foreach ($ids as $com_id) {
            $comment = $em->getRepository('mycpBundle:comment')->find($com_id);
            $comment->setComPublic(true);
            $em->persist($comment);
            $em->flush();

            $ownership = $comment->getComOwnership();
            $em->getRepository("mycpBundle:ownership")->updateRanking($ownership);
            $em->getRepository("mycpBundle:ownership")->updateRating($ownership);
        }

    }

    public function deleteMultiples($ids) {
        $em = $this->getEntityManager();
        foreach ($ids as $com_id) {
            $comment = $em->getRepository('mycpBundle:comment')->find($com_id);
            $em->remove($comment);
            $em->flush();

            $ownership = $comment->getComOwnership();
            $em->getRepository("mycpBundle:ownership")->updateRanking($ownership);
            $em->getRepository("mycpBundle:ownership")->updateRating($ownership);
        }
    }

    function getByUserOwnership($id_user, $id_ownership, $date) {
        if ($id_user != null) {
            $em = $this->getEntityManager();

            $query_string = "SELECT com FROM mycpBundle:comment com
                             WHERE com.com_user = :id_user
                             AND com.com_ownership = :id_ownership
                             AND com.com_date >= :date
                             ORDER BY com.com_date DESC";

            return $em->createQuery($query_string)
                    ->setParameter('id_user', $id_user)
                    ->setParameter('id_ownership', $id_ownership)
                    ->setParameter('date', $date)
                    ->getResult();
        }
    }

    function getCommentsByAccommodation($olders, $date = null, $startIndex = 0, $maxResults = 500)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("comment")
            ->from("mycpBundle:comment", "comment")
            ->setFirstResult($startIndex)
            ->setMaxResults($maxResults);

        if($date != null)
        {
            if($olders)
                $qb->andWhere("comment.com_date <= :date")->setParameter('date',$date);
            else
                $qb->andWhere("comment.com_date >= :date")->setParameter('date',$date);
        }

        return $qb->getQuery()->getResult();
    }

    public function isFromUserWithReservations(comment $comment){
        $em = $this->getEntityManager();

        $queryStr = "SELECT COUNT(res) FROM mycpBundle:generalReservation res
                JOIN res.own_reservations r
                JOIN r.own_res_reservation_booking b
                JOIN b.payments p
                WHERE res.gen_res_user_id = :userId
                AND res.gen_res_own_id = :accommodationId
                AND res.gen_res_status = ".generalReservation::STATUS_RESERVED."
                AND (p.status = ".PaymentHelper::STATUS_SUCCESS." OR p.status = ".PaymentHelper::STATUS_PROCESSED.")";

        return $em->createQuery($queryStr)
            ->setParameter('userId', $comment->getComUser()->getUserId())
            ->setParameter('accommodationId', $comment->getComOwnership()->getOwnId())
            ->getOneOrNullResult();
    }

}
