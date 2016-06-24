<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\cart;

/**
 * cartRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class cartRepository extends EntityRepository {

    public function getCartItems($user_ids) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT c FROM mycpBundle:cart c JOIN c.cart_room r JOIN r.room_ownership o ";
            $where = "";

            if ($user_ids["user_id"] != null)
                $where.= " WHERE c.cart_user = " . $user_ids['user_id'];
            else if ($user_ids["session_id"] != null)
                $where .= " WHERE c.cart_session_id = '" . $user_ids["session_id"] . "'";

            $orderBy = " ORDER BY o.own_id ASC";

            if ($where != "")
                return $em->createQuery($query_string . $where . $orderBy)->getResult();
            else
                return null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getCartItemsForCheck($user_ids) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT c, room, o FROM mycpBundle:cart c JOIN c.cart_room room JOIN room.room_ownership o";
            $where = "";

            if ($user_ids["user_id"] != null)
                $where.= " WHERE c.cart_user = " . $user_ids['user_id'];
            else if ($user_ids["session_id"] != null)
                $where .= " WHERE c.cart_session_id = '" . $user_ids["session_id"] . "'";

            if ($where != "")
                return $em->createQuery($query_string . $where . " ORDER BY o.own_id ASC, c.cart_date_from ASC")->getResult();
            else
                return null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getCartItemsByUser($userId) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT c FROM mycpBundle:cart c WHERE c.cart_user = " . $userId;

            return $em->createQuery($query_string)->getResult();
        } catch (Exception $e) {
            return null;
        }
    }

    public function isFullCartForReminder($user_id = null, $sessionId = null) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT c FROM mycpBundle:cart c ";

            $where = "";

            if ($user_id != null)
                $where = "WHERE c.cart_user = " . $user_id;
            else if ($sessionId != null)
                $where = "WHERE c.cart_user is not NULL AND c.cart_session_id = '" . $sessionId . "'";

            $date = new \DateTime();
            $where .= " AND c.cart_created_date <= '" . date("Y-m-d H:i:s", strtotime("-6 hours", $date->getTimestamp())) . "'";

            return count($em->createQuery($query_string . $where)->getResult());
        } catch (Exception $e) {
            return null;
        }
    }

    public function getUserFromCart($user_id = null, $sessionId = null) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT DISTINCT u.user_id FROM mycpBundle:cart c JOIN c.cart_user u ";

            $where = "";

            if ($user_id != null)
                $where = "WHERE c.cart_user = " . $user_id;
            else if ($sessionId != null)
                $where = "WHERE c.cart_user IS NOT NULL AND c.cart_session_id = '" . $sessionId . "'";

            $cartUser = $em->createQuery($query_string . $where)->getResult();

            if (count($cartUser) > 0) {
                $user = $em->getRepository("mycpBundle:user")->find($cartUser[0]["user_id"]);
                return $user;
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function countItems($user_ids) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT COUNT(DISTINCT r.room_id) FROM mycpBundle:cart c JOIN c.cart_room r";
            $where = "";

            if ($user_ids["user_id"] != null)
                $where.= " WHERE c.cart_user = " . $user_ids['user_id'];
            else if ($user_ids["session_id"] != null)
                $where .= " WHERE c.cart_session_id = '" . $user_ids["session_id"] . "'";

            if ($where != "")
                return $em->createQuery($query_string . $where)->getSingleScalarResult();
            else
                return 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function emptyCart($user_ids) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT c FROM mycpBundle:cart c ";
            $where = "";
            $cartItems = null;

            if ($user_ids["user_id"] != null)
                $where.= " WHERE c.cart_user = " . $user_ids['user_id'];
            else if ($user_ids["session_id"] != null)
                $where .= " WHERE c.cart_session_id = '" . $user_ids["session_id"] . "'";

            if ($where != "")
                $cartItems = $em->createQuery($query_string . $where)->getResult();

            foreach ($cartItems as $item) {
                $em->remove($item);
            }
            $em->flush();
        } catch (Exception $e) {

        }
    }

    public function setToUser($user, $session_id) {
        $em = $this->getEntityManager();
        $to_set = $em->getRepository("mycpBundle:cart")->findBy(array('cart_session_id' => $session_id,
            'cart_user' => null));

        foreach ($to_set as $cartItem) {
            if ($cartItem->getCartRoom() != null)
                $count = count($em->getRepository('mycpBundle:cart')->findBy(array('cart_user' => $user->getUserId(),
                            'cart_room' => $cartItem->getCartRoom(),
                            'cart_date_from' => $cartItem->getCartDateFrom(),
                            'cart_date_to' => $cartItem->getCartDateTo())));

            if ($count == 0) {
                $cartItem->setCartUser($user);
                $em->persist($cartItem);
            }
            else
                $em->remove($cartItem);
        }
        $em->flush();
    }

    public function testValues($testUser) {
        $em = $this->getEntityManager();
        $cartItems = array();
        $room = $em->getRepository("mycpBundle:room")->findOneBy(array("room_type" => "Habitación Triple"));

        $cart = new cart();
        $cart->setCartCountAdults(2);
        $cart->setCartCountChildren(1);
        $cart->setCartCreatedDate(new \DateTime());
        $date = new \DateTime();
        $dateFrom = $date->setTimestamp(strtotime("-1 day", $date->getTimestamp()));
        $date = new \DateTime();
        $dateTo = $date->setTimestamp(strtotime("+4 day", $date->getTimestamp()));
        $cart->setCartDateFrom($dateFrom);
        $cart->setCartDateTo($dateTo);
        $cart->setCartRoom($room);
        $cart->setCartUser($testUser);

        array_push($cartItems, $cart);

        return $cartItems;
    }

}
