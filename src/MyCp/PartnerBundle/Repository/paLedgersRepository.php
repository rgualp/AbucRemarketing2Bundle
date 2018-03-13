<?php

namespace MyCp\PartnerBundle\Repository;
use MyCp\mycpBundle\Helpers\Dates;
use Doctrine\ORM\EntityRepository;
use MyCp\PartnerBundle\Entity\paAccountLedgers;

/**
 * paContactRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class paLedgersRepository extends EntityRepository {

    function getLastLedger($account_id) {

        return $this->createQueryBuilder('e')
            ->where("e.account= :account_id")
            ->setParameter("account_id", $account_id)
            ->orderBy('e.created', 'DESC')->
            setMaxResults(1)->
            getQuery()->
            getOneOrNullResult();}
    function getLastLedgerCas($account_id) {

        return $this->createQueryBuilder('e')
            ->where("e.account= :account_id")

            ->setParameter("account_id", $account_id)
            ->orderBy('e.created', 'DESC')->
            setMaxResults(1)->
            getQuery()->
            getOneOrNullResult();}

    function getallLedger($account_id,$dates) {

        $to_between = (array_key_exists('to_between', $dates) && isset($dates['to_between']));
        $qb= $this->createQueryBuilder('e')
            ->where("e.account= :account_id")
            ->setParameter("account_id", $account_id)

            ;


        if($to_between) {
            $qb->andWhere('e.created >= :date_a');
            $qb->andWhere('e.created <= :date_b');
            $qb->setParameter('date_a', Dates::createForQuery($dates['to_between'][0], 'd-m-Y'));
            $qb->setParameter('date_b', Dates::createForQuery($dates['to_between'][1], 'd-m-Y'));
            $qb->orderBy('e.created','ASC');
        }
        return $qb->getQuery()->getArrayResult();
    }

}
