<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ownershipStatRepository
 *
 */
class ownershipStatRepository extends EntityRepository
{
    public function getBulb($nomenclatorStatParent=null,$province=null,$municipality=null ){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
            if($municipality==null) {
                $qb->select('os','sn', 'np','SUM(os.stat_value) AS stat_value');
               $qb->groupBy('sn.nom_id');
                if($province!=null){
                    $municipalities = $em->getRepository('mycpBundle:municipality')->findBy(array('mun_prov_id'=>$province));
                    $qb->where('os.stat_municipality in (:mun)')->setParameter('mun',(array)$municipalities);
                }
            }
            else{
                $qb->select('os','sn', 'np','os.stat_value AS stat_value');
                $qb->where("os.stat_municipality = :municipalityId")
                    ->setParameter("municipalityId", $municipality->getMunId());

            }

            $qb->from("mycpBundle:ownershipStat", "os")
            ->join("os.stat_nomenclator", "sn")
            ->join("sn.nom_parent", "np");
           $qb->orderBy('np.nom_id', 'ASC');
           $qb->addOrderBy('sn.nom_id', 'ASC');
            if($nomenclatorStatParent!=null){
                $qb->where('sn.nom_parent = :nomStat')
            ->setParameter('nomStat',$nomenclatorStatParent);}
        return $qb->getQuery()->getResult();
    }

    public function getData($nomenclatorStat, $municipality = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
             ->select('os')
             ->from("mycpBundle:ownershipStat", "os")
             ->where("os.stat_nomenclator = :nomenclatorId")
             ->setParameter("nomenclatorId", $nomenclatorStat->getNomId());

        if($municipality != null) {
            $qb->andWhere("os.stat_municipality = :municipalityId")
               ->setParameter("municipalityId", $municipality->getMunId());
        }

        return $qb->getQuery()->getResult();
    }

    public function getDataForProvince($nomenclatorStat, $province)
    {
        $em = $this->getEntityManager();
        $municipalities = $em->getRepository('mycpBundle:municipality')->findBy(array('mun_prov_id'=>$province));
        $qb = $em->createQueryBuilder()
             ->select('os')
             ->from("mycpBundle:ownershipStat", "os")
             ->where("os.stat_nomenclator = :nomenclatorId")
             ->setParameter("nomenclatorId", $nomenclatorStat->getNomId());
       $qb->andWhere("os.stat_municipality  IN (:municipalities)")
           ->setParameter("municipalities", $municipalities);
//        foreach($municipalities as $municipality) {
//            $qb->a("os.stat_municipality = :municipalityId")
//               ->setParameter("municipalityId", $municipality->getMunId());
//        }

        return $qb->getQuery()->getResult();
    }

    public function insertOrUpdate($nomenclator, $municipality, $value)
    {
        $em = $this->getEntityManager();
        $stat = $em->getRepository("mycpBundle:ownershipStat")->findOneBy(array("stat_municipality" => $municipality->getMunId(), "stat_nomenclator" => $nomenclator->getNomId()));

        if($stat === null)
            $stat = new ownershipStat();

        $stat->setStatNomenclator($nomenclator);
        $stat->setStatMunicipality($municipality);
        $stat->setStatValue($value);

        $em->persist($stat);
        $em->flush();
    }

    public function insertOrUpdateObj(ownershipStat $stat)
    {   $em = $this->getEntityManager();
        $statDb = $em->getRepository("mycpBundle:ownershipStat")->findOneBy(array("stat_municipality" => $stat->getStatMunicipality(), "stat_nomenclator" => $stat->getStatNomenclator()));

        if($statDb === null)
            $statDb = new ownershipStat();

        $statDb->setStatNomenclator($stat->getStatNomenclator());
        $statDb->setStatMunicipality($stat->getStatMunicipality());
        $statDb->setStatValue($stat->getStatValue());

        $em->persist($statDb);
       // $em->flush();
    }

    function getMunicipalities(){
       $em = $this->getEntityManager();
       $municipalities = $em->getRepository('mycpBundle:municipality')->findAll();
       return $municipalities;
   }

    public function getOwnershipTotalsByStatus($municipalities=null)
    {
        $em = $this->getEntityManager();
        if(!$municipalities)
        $municipalities = $em->getRepository('mycpBundle:destination')->getByMunicipality();
        $ownershipRepository=$em->getRepository("mycpBundle:ownership");
        $status=$em->getRepository("mycpBundle:ownershipStatus")->findAll();
        $nomenclatorRepository=$em->getRepository("mycpBundle:nomenclatorStat");
        $result = array();
        //Status 1-Activo, 2-Inactivo, 3-En proceso, 4-Eliminar y 5-En proceso(por lotes)
        foreach($municipalities as $municipality){
            foreach($status as $st){
                $owns=  $ownershipRepository->findBy(array('own_address_municipality'=>$municipality, 'own_status'=>$st));
                $nomStat=$nomenclatorRepository->findOneBy(array('nom_name'=>$st->getStatusName()));
                if($nomStat == null)
                {
                    $parent=$nomenclatorRepository->findOneBy(array('nom_name'=>"Estados"));
                    $nomStat = new nomenclatorStat();
                    $nomStat->setNomParent($parent)
                        ->setNomName($st->getStatusName());

                    $em->persist($nomStat);
                    $em->flush();
                }

                $ownStat=new ownershipStat();
                $ownStat->setStatMunicipality($municipality);
                $ownStat->setStatNomenclator($nomStat);
                $ownStat->setStatValue(count($owns));
                $result[]=$ownStat;
            }
        }
        return $result;

    }

    public function getOwnershipTotalsByType($municipalities=null)
    {  // $types=['Casa particular','Propiedad completa', 'Apartamento', 'Penthouse','Villa con piscina'];

        $em = $this->getEntityManager();
        if(!$municipalities)
        $municipalities = $em->getRepository('mycpBundle:destination')->getByMunicipality();
        $ownershipRepository=$em->getRepository("mycpBundle:ownership");
        $nomenclatorRepository=$em->getRepository("mycpBundle:nomenclatorStat");
        $nomenType=$nomenclatorRepository->findOneBy(array('nom_name'=>'Tipos'));
        $nomenTypes=$nomenclatorRepository->findBy(array('nom_parent'=>$nomenType));
        $result = array();
        foreach($municipalities as $municipality){
            foreach($nomenTypes as $ownType){
                $owns=  $ownershipRepository->findBy(array('own_address_municipality'=>$municipality, 'own_type'=>$ownType->getNomName()));
                $ownStat=new ownershipStat();
                $ownStat->setStatMunicipality($municipality);
                $ownStat->setStatNomenclator($ownType);
                $ownStat->setStatValue(count($owns));
                $result[]=$ownStat;
            }
        }
        return $result;
    }

    public function getOwnershipTotalsByCategory($municipalities=null)
    {   $em = $this->getEntityManager();
        if(!$municipalities)
        $municipalities = $em->getRepository('mycpBundle:destination')->getByMunicipality();
        $ownershipRepository=$em->getRepository("mycpBundle:ownership");
        $nomenclatorRepository=$em->getRepository("mycpBundle:nomenclatorStat");
        $nomenCat=$nomenclatorRepository->findOneBy(array('nom_name'=>'Categorías'));
        $nomenCats=$nomenclatorRepository->findBy(array('nom_parent'=>$nomenCat));
        $result = array();
        foreach($municipalities as $municipality){
            foreach($nomenCats as $ownCat){
                $owns=  $ownershipRepository->findBy(array('own_address_municipality'=>$municipality, 'own_category'=>$ownCat->getNomName()));
                $ownStat=new ownershipStat();
                $ownStat->setStatMunicipality($municipality);
                $ownStat->setStatNomenclator($ownCat);
                $ownStat->setStatValue(count($owns));
                $result[]=$ownStat;
            }
        }
        return $result;
    }

    public function getOwnershipTotalsBySummary($municipalities=nulls)
    { //total, selection, reserva_inmediata
        $em = $this->getEntityManager();
        if(!$municipalities)
        $municipalities = $em->getRepository('mycpBundle:destination')->getByMunicipality();
        $ownershipRepository=$em->getRepository("mycpBundle:ownership");
        $nomenclatorRepository=$em->getRepository("mycpBundle:nomenclatorStat");
        $result = array();
        foreach($municipalities as $municipality){
            $nomTotal=$nomenclatorRepository->findOneBy(array('nom_name'=>'Total'));
            $nomCasaSelec=$nomenclatorRepository->findOneBy(array('nom_name'=>'Casa Selección'));
            $nomReservRapida=$nomenclatorRepository->findOneBy(array('nom_name'=>'Reserva Rápida'));
            $nomReservInm=$nomenclatorRepository->findOneBy(array('nom_name'=>'Reserva Inmediata'));

            $owns=  $ownershipRepository->findBy(array('own_address_municipality'=>$municipality));
            $ownStat=new ownershipStat();
            $ownStat->setStatMunicipality($municipality);
            $ownStat->setStatNomenclator($nomTotal);
            $ownStat->setStatValue(count($owns));
            $result[]=$ownStat;

            $owns=  $ownershipRepository->findBy(array('own_address_municipality'=>$municipality, 'own_selection'=>true));
            $ownStat=new ownershipStat();
            $ownStat->setStatMunicipality($municipality);
            $ownStat->setStatNomenclator($nomCasaSelec);
            $ownStat->setStatValue(count($owns));
            $result[]=$ownStat;

            $owns=  $ownershipRepository->findBy(array('own_address_municipality'=>$municipality, 'own_inmediate_booking'=>true));
            $ownStat=new ownershipStat();
            $ownStat->setStatMunicipality($municipality);
            $ownStat->setStatNomenclator($nomReservRapida);
            $ownStat->setStatValue(count($owns));
            $result[]=$ownStat;

            $owns=  $ownershipRepository->findBy(array('own_address_municipality'=>$municipality, 'own_inmediate_booking_2'=>true));
            $ownStat=new ownershipStat();
            $ownStat->setStatMunicipality($municipality);
            $ownStat->setStatNomenclator($nomReservInm);
            $ownStat->setStatValue(count($owns));
            $result[]=$ownStat;
        }
        return $result;


    }

    public function getOwnershipTotalsByLanguage($municipalities=null)
    {   $em = $this->getEntityManager();
        if(!$municipalities)
            $municipalities = $em->getRepository('mycpBundle:destination')->getByMunicipality();
        $ownershipRepository=$em->getRepository("mycpBundle:ownership");
        $nomenclatorRepository=$em->getRepository("mycpBundle:nomenclatorStat");
        $nomenIngles=$nomenclatorRepository->findOneBy(array('nom_name'=>'Inglés'));
        $nomenFr=$nomenclatorRepository->findOneBy(array('nom_name'=>'Francés'));
        $nomenGer=$nomenclatorRepository->findOneBy(array('nom_name'=>'Alemán'));
        $nomenIta=$nomenclatorRepository->findOneBy(array('nom_name'=>'Italiano'));

        $result = array();
        foreach($municipalities as $municipality){
               $owns=  $ownershipRepository->findBy(array('own_address_municipality'=>$municipality));
                $ownStatIng=new ownershipStat();
            $ownStatIng->setStatMunicipality($municipality);
            $ownStatIng->setStatNomenclator($nomenIngles);
            $ownStatIng->setStatValue(0);
            $ownStatFr=new ownershipStat();
            $ownStatFr->setStatMunicipality($municipality);
            $ownStatFr->setStatNomenclator($nomenFr);
            $ownStatFr->setStatValue(0);
            $ownStatGer=new ownershipStat();
            $ownStatGer->setStatMunicipality($municipality);
            $ownStatGer->setStatNomenclator($nomenGer);
            $ownStatGer->setStatValue(0);
            $ownStatIta=new ownershipStat();
            $ownStatIta->setStatMunicipality($municipality);
            $ownStatIta->setStatNomenclator($nomenIta);
            $ownStatIta->setStatValue(0);

            foreach($owns as $own){
                $langs=$own->getOwnLangs();
                if(substr($langs,0,1)=='1')
                    $ownStatIng->setStatValue((int)$ownStatIng->getStatValue()+1);
                if(substr($langs,1,1)=='1')
                    $ownStatFr->setStatValue((int)$ownStatFr->getStatValue()+1);
                if(substr($langs,2,1)=='1')
                    $ownStatGer->setStatValue((int)$ownStatGer->getStatValue()+1);
                if(substr($langs,3,1)=='1')
                    $ownStatIta->setStatValue((int)$ownStatIta->getStatValue()+1);

            }
            $result[]=$ownStatIng;
            $result[]=$ownStatFr;
            $result[]=$ownStatGer;
            $result[]=$ownStatIta;

            }

        return $result;


    }

    public function getOwnershipTotalsByRoomsNumber($municipalities=null)
    {   $em = $this->getEntityManager();
        if(!$municipalities)
            $municipalities = $em->getRepository('mycpBundle:destination')->getByMunicipality();
        $ownershipRepository=$em->getRepository("mycpBundle:ownership");
        $nomenclatorRepository=$em->getRepository("mycpBundle:nomenclatorStat");
        $nomenRoom=$nomenclatorRepository->findOneBy(array('nom_name'=>'Total de Habitaciones'));
        $nomenRooms=$nomenclatorRepository->findBy(array('nom_parent'=>$nomenRoom));
        $result = array();
        foreach($municipalities as $municipality){
            $ownsRest= $ownershipRepository->getByRoomsTotalAndMunicipality($municipality);
            foreach($nomenRooms as $ownCat){
                if(($ownCat->getNomName()!='más 5')) {
                    $owns = $ownershipRepository->getByRoomsTotalAndMunicipality($municipality,(int)$ownCat->getNomName());
                    $ownsRest=array_diff($ownsRest,$owns);

                $ownStat=new ownershipStat();
                $ownStat->setStatMunicipality($municipality);
                $ownStat->setStatNomenclator($ownCat);
                $ownStat->setStatValue(count($owns));
                $result[]=$ownStat;
                }
                else{
                    $ownStat=new ownershipStat();
                    $ownStat->setStatMunicipality($municipality);
                    $ownStat->setStatNomenclator($ownCat);
                    $ownStat->setStatValue(count($ownsRest));
                    $result[]=$ownStat;
                }

            }
        }
        return $result;


    }

    public function getOwnershipReportListContent($nomenclator, $province=null, $municipality=null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select("o")
           ->from("mycpBundle:ownership", "o")
           ->join("o.own_address_province", "prov");

        if($municipality==null) {
            if($province!=null){
                $qb->where('o.own_address_province = :province')->setParameter('province',$province->getProvId());
            }
        }
        else{
            $qb->where("o.own_address_municipality = :municipalityId")
                ->setParameter("municipalityId", $municipality->getMunId());
        }

        $qb->orderBy("prov.prov_name", "ASC")
           ->addOrderBy("o.own_mcp_code", "ASC");

        $qb = $this->addNomenclatorWheres($qb, $nomenclator);

        return $qb->getQuery()->getResult();
    }

    private function addNomenclatorWheres($queryBuilder, $nomenclator)
    {
        if($nomenclator->getNomParent() != null) {
            switch ($nomenclator->getNomParent()->getNomName()){
                case "Estados": $queryBuilder->join("o.own_status", "st")->andWhere("st.status_name = :value")->setParameter("value", $nomenclator->getNomName()); break;
                case "Tipos": $queryBuilder->andWhere("o.own_type = :value")->setParameter("value", $nomenclator->getNomName()); break;
                case "Categorías": $queryBuilder->andWhere("o.own_category = :value")->setParameter("value", $nomenclator->getNomName()); break;
                case "Total de Habitaciones":
                {
                    if($nomenclator->getNomName() != "más 5")
                        $queryBuilder->andWhere("o.own_rooms_total = :value")->setParameter("value", $nomenclator->getNomName());
                    else
                        $queryBuilder->andWhere("o.own_rooms_total > 5");
                    break;
                }
                case "Idiomas": $queryBuilder = $this->addLanguageNomenclatorWhere($nomenclator->getNomName(), $queryBuilder); break;
                case "Resúmen": $queryBuilder = $this->addSummaryNomenclatorWhere($nomenclator->getNomName(), $queryBuilder); break;
            }
        }
        return $queryBuilder;
    }

    private function addLanguageNomenclatorWhere($nomenclatorName, $queryBuilder)
    {
        switch($nomenclatorName)
        {
            case "Inglés": $queryBuilder->andWhere("o.own_langs LIKE '1___'"); break;
            case "Francés": $queryBuilder->andWhere("o.own_langs LIKE '_1__'"); break;
            case "Alemán": $queryBuilder->andWhere("o.own_langs LIKE '__1_'"); break;
            case "Italiano": $queryBuilder->andWhere("o.own_langs LIKE '___1'"); break;
        }

        return $queryBuilder;
    }

    private function addSummaryNomenclatorWhere($nomenclatorName, $queryBuilder)
    {
        switch($nomenclatorName)
        {
            case "Casa Selección": $queryBuilder->andWhere("o.own_selection = 1"); break;
            case "Reserva Rápida": $queryBuilder->andWhere("o.own_inmediate_booking = 1"); break;
            case "Reserva Inmediata": $queryBuilder->andWhere("o.own_inmediate_booking_2 = 1"); break;
        }

        return $queryBuilder;
    }
}
