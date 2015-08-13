<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ownershipReservationStatRepository
 *
 */
class ownershipReservationStatRepository extends EntityRepository
{
    public function getAccommodations($nomenclatorStatParent=null,$province=null,$municipality=null, $destination = null, $dateFrom = null, $dateTo = null ){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $hasLocation = false;

        if($destination == null) {
            if ($municipality == null) {
                $qb->select('os', 'o')->groupBy('os.stat_accommodation');
                if ($province != null) {
                    $municipalities = $em->getRepository('mycpBundle:municipality')->findBy(array('mun_prov_id' => $province));
                    $qb->where('o.own_address_municipality in (:mun)')->setParameter('mun', (array)$municipalities);
                    $hasLocation = true;
                }
            } else {
                $qb->select('os', 'o');
                $qb->where("o.own_address_municipality = :municipalityId")
                    ->setParameter("municipalityId", $municipality->getMunId());
                $hasLocation = true;
            }
        }
        else
        {
            $qb->select('os', 'o');
            $qb->where("o.own_destination = :destination")
                ->setParameter("destination", $destination->getDesId());
            $hasLocation = true;
        }

        if($dateFrom != null)
        {
            if(!$hasLocation)
                $qb->where("os.stat_date_from >= :dateFrom")
                    ->setParameter("dateFrom", $dateFrom);
            else
                $qb->andWhere("os.stat_date_from >= :dateFrom")
                    ->setParameter("dateFrom", $dateFrom);
        }

        if($dateTo != null)
        {
            $qb->andWhere("os.stat_date_to <= :dateTo")
                ->setParameter("dateTo", $dateTo);
        }

            $qb->from("mycpBundle:ownershipReservationStat", "os")
            ->join("os.stat_accommodation", "o")
            ->orderBy("o.own_address_province", "ASC")
            ->addOrderBy("o.own_automatic_mcp_code");

        return $qb->getQuery()->getResult();
    }

    public function getBulb($nomenclatorStatParent=null,$province=null,$municipality=null, $destination = null, $dateFrom = null, $dateTo = null, $ownership = null ){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $hasLocation = false;
        if($destination == null) {
            if ($municipality == null) {
                $qb->select('os', 'sn', 'np', 'SUM(os.stat_value) AS stat_value', 'o');
                $qb->groupBy('sn.nom_id');
                if ($province != null) {
                    $municipalities = $em->getRepository('mycpBundle:municipality')->findBy(array('mun_prov_id' => $province));
                    $qb->where('o.own_address_municipality in (:mun)')->setParameter('mun', (array)$municipalities);
                    $hasLocation = true;
                }
            } else {
                $qb->select('os', 'sn', 'np', 'SUM(os.stat_value) AS stat_value', 'o');
                $qb->where("o.own_address_municipality = :municipalityId")
                    ->setParameter("municipalityId", $municipality->getMunId());
                $hasLocation = true;
            }
        }
        else
        {
            $qb->select('os','sn', 'np','SUM(os.stat_value) AS stat_value', 'o');
            $qb->where("o.own_destination = :destination")
                ->setParameter("destination", $destination->getDesId());
            $hasLocation = true;
        }

        if($dateFrom != null)
        {
            if(!$hasLocation)
                $qb->where("os.stat_date_from >= :dateFrom")
                    ->setParameter("dateFrom", $dateFrom);
            else
                $qb->andWhere("os.stat_date_from >= :dateFrom")
                    ->setParameter("dateFrom", $dateFrom);
        }

        if($dateTo != null)
        {
            $qb->andWhere("os.stat_date_to <= :dateTo")
                ->setParameter("dateTo", $dateTo);
        }

        if($ownership != null)
        {
            $qb->andWhere("os.stat_accommodation = :ownership")
                ->setParameter("ownership", $ownership);
        }

        $qb->from("mycpBundle:ownershipReservationStat", "os")
            ->join("os.stat_nomenclator", "sn")
            ->join("sn.nom_parent", "np")
            ->join("os.stat_accommodation", "o");
        $qb->orderBy('np.nom_id', 'ASC');
        $qb->addOrderBy('sn.nom_id', 'ASC');

        if($nomenclatorStatParent!=null){
            $qb->where('sn.nom_parent = :nomStat')
                ->setParameter('nomStat',$nomenclatorStatParent);}

        return $qb->getQuery()->getResult();
    }

    /*public function getData($nomenclatorStat, $municipality = null)
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
*/
    public function insertOrUpdate($nomenclator, $ownership, $date_from, $date_to, $value)
    {
        $em = $this->getEntityManager();
        $stat = $em->getRepository("mycpBundle:ownershipReservationStat")->findOneBy(array("stat_accommodation" => $ownership->getOwnId(),
            "stat_nomenclator" => $nomenclator->getNomId(), "stat_date_from" => $date_from, "stat_date_to" => $date_to));

        if($stat === null)
        {
            $stat = new ownershipReservationStat();
            $stat->setStatNomenclator($nomenclator);
            $stat->setStatOwnership($ownership);
            $stat->setStatDateFrom($date_from);
            $stat->setStatDateTo($date_to);
        }

        $value = (float)$stat->getStatValue() + $value;
        $stat->setStatValue($value);

        $em->persist($stat);
        $em->flush();
    }

    public function insertOrUpdateObj(ownershipReservationStat $stat)
    {   $em = $this->getEntityManager();
        $statDb = $em->getRepository("mycpBundle:ownershipReservationStat")->findOneBy(array("stat_accommodation" => $stat->getStatAccommodation(),
            "stat_nomenclator" => $stat->getStatNomenclator(), "stat_date_from" => $stat->getStatDateFrom(), "stat_date_to" => $stat->getStatDateTo()));

        if($statDb === null)
            $statDb = new ownershipReservationStat();

        $statDb->setStatNomenclator($stat->getStatNomenclator());
        $statDb->setStatAccommodation($stat->getStatAccommodation());
        $statDb->setStatDateFrom($stat->getStatDateFrom());
        $statDb->setStatDateTo($stat->getStatDateTo());

        $value = ($statDb->getStatValue() == null)? 0: (float)$statDb->getStatValue();
        $statDb->setStatValue($value + (float)$stat->getStatValue());

        $em->persist($statDb);
        $em->flush();
    }

    function getMunicipalities(){
       $em = $this->getEntityManager();
       $municipalities = $em->getRepository('mycpBundle:municipality')->findAll();
       return $municipalities;
   }

    public function calculateStats($ownership, $date, $timer)
    {
        $em = $this->getEntityManager();
        $resRepository=$em->getRepository("mycpBundle:ownershipReservation");
        $nomenclatorRepository=$em->getRepository("mycpBundle:nomenclatorStat");
        $nomReservations=$nomenclatorRepository->findOneBy(array('nom_name'=>'Solicitudes'));
        $nomReceived=$nomenclatorRepository->findOneBy(array('nom_name'=>'Recibidas', "nom_parent" => $nomReservations));
        $nomNonAvailable=$nomenclatorRepository->findOneBy(array('nom_name'=>'No disponibles', "nom_parent" => $nomReservations));
        $nomAvailable=$nomenclatorRepository->findOneBy(array('nom_name'=>'Marcadas como disponibles', "nom_parent" => $nomReservations));
        $nomReserved=$nomenclatorRepository->findOneBy(array('nom_name'=>'Reservadas', "nom_parent" => $nomReservations));
        $nomOutdated=$nomenclatorRepository->findOneBy(array('nom_name'=>'Vencidas', "nom_parent" => $nomReservations));

        $nomRent=$nomenclatorRepository->findOneBy(array('nom_name'=>'Hospedaje'));
        $nomGuests=$nomenclatorRepository->findOneBy(array('nom_name'=>'Huéspedes recibidos', "nom_parent" => $nomRent));
        $nomNights=$nomenclatorRepository->findOneBy(array('nom_name'=>'Noches reservadas', "nom_parent" => $nomRent));
        $nomRooms=$nomenclatorRepository->findOneBy(array('nom_name'=>'Habitaciones reservadas', "nom_parent" => $nomRent));

        $nomComment=$nomenclatorRepository->findOneBy(array('nom_name'=>'Comentarios'));
        $nomCommentsTotal=$nomenclatorRepository->findOneBy(array('nom_name'=>'Total', "nom_parent" => $nomComment));

        $nomIncomes=$nomenclatorRepository->findOneBy(array('nom_name'=>'Ingresos'));
        $nomPossibleIncomesTotal=$nomenclatorRepository->findOneBy(array('nom_name'=>'Posibles inglesos totales', "nom_parent" => $nomIncomes));
        $nomAccommodationRealIncomes=$nomenclatorRepository->findOneBy(array('nom_name'=>'Ingresos reales (Casa)', "nom_parent" => $nomIncomes));
        $nomMyCPRealIncomes=$nomenclatorRepository->findOneBy(array('nom_name'=>'Ingresos reales (MyCP)', "nom_parent" => $nomIncomes));

        $result = array();
        $reservations=  $resRepository->getAllReservations($ownership, $date);

        foreach($reservations as $reservation) {
            //Total de reservaciones recibidas por estados
            $stat = new ownershipReservationStat();
            $stat->setStatAccommodation($ownership)
                ->setStatNomenclator($nomReceived)
                ->setStatValue(1)
                ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                ->setStatDateTo($reservation->getOwnResReservationToDate());
            $result[] = $stat;

            //Ingresos posibles: Ingresos totales (Casa + MyCP) si se hubieran confirmado todas las Solicitudes recibidas para esta casa.
            $stat = new ownershipReservationStat();
            $stat->setStatAccommodation($ownership)
                ->setStatNomenclator($nomPossibleIncomesTotal)
                ->setStatValue($reservation->getOwnResTotalInSite())
                ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                ->setStatDateTo($reservation->getOwnResReservationToDate());
            $result[] = $stat;

            switch($reservation->getOwnResStatus())
            {
                case ownershipReservation::STATUS_NOT_AVAILABLE:{
                    $stat = new ownershipReservationStat();
                    $stat->setStatAccommodation($ownership)
                        ->setStatNomenclator($nomNonAvailable)
                        ->setStatValue(1)
                        ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                        ->setStatDateTo($reservation->getOwnResReservationToDate());
                    $result[] = $stat;
                    break;
                }
                case ownershipReservation::STATUS_RESERVED:{
                    $stat = new ownershipReservationStat();
                    $stat->setStatAccommodation($ownership)
                        ->setStatNomenclator($nomReserved)
                        ->setStatValue(1)
                        ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                        ->setStatDateTo($reservation->getOwnResReservationToDate());
                    $result[] = $stat;

                    $stat = new ownershipReservationStat();
                    $stat->setStatAccommodation($ownership)
                        ->setStatNomenclator($nomAvailable)
                        ->setStatValue(1)
                        ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                        ->setStatDateTo($reservation->getOwnResReservationToDate());
                    $result[] = $stat;

                    //Total de habitaciones reservadas
                    $countRooms = count($em->getRepository("mycpBundle:ownershipReservation")->findBy(array("own_res_gen_res_id" => $reservation->getOwnResGenResId()->getGenResId())));
                    $stat = new ownershipReservationStat();
                    $stat->setStatAccommodation($ownership)
                        ->setStatNomenclator($nomRooms)
                        ->setStatValue($countRooms)
                        ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                        ->setStatDateTo($reservation->getOwnResReservationToDate());
                    $result[] = $stat;

                    //Huespedes recibidos
                    $stat = new ownershipReservationStat();
                    $stat->setStatAccommodation($ownership)
                        ->setStatNomenclator($nomGuests)
                        ->setStatValue($reservation->getOwnResCountAdults() + $reservation->getOwnResCountChildrens())
                        ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                        ->setStatDateTo($reservation->getOwnResReservationToDate());
                    $result[] = $stat;

                    //Noches reservadas
                    $stat = new ownershipReservationStat();
                    $stat->setStatAccommodation($ownership)
                        ->setStatNomenclator($nomNights)
                        ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                        ->setStatDateTo($reservation->getOwnResReservationToDate());

                    if($reservation->getOwnResNights() != null || $reservation->getOwnResNights() > 0)
                        $stat->setStatValue($reservation->getOwnResNights());
                    else
                    {
                        $nightsTotal = $timer->nights($reservation->getOwnResReservationFromDate()->getTimestamp(), $reservation->getOwnResReservationToDate()->getTimestamp());
                        $stat->setStatValue($nightsTotal);
                    }
                    $result[] = $stat;

                    //Ingresos Reales Casa
                    $mycpCommission = $ownership->getOwnCommissionPercent() * $reservation->getOwnResTotalInSite() / 100;
                    $stat = new ownershipReservationStat();
                    $stat->setStatAccommodation($ownership)
                        ->setStatNomenclator($nomAccommodationRealIncomes)
                        ->setStatValue($reservation->getOwnResTotalInSite() - $mycpCommission)
                        ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                        ->setStatDateTo($reservation->getOwnResReservationToDate());
                    $result[] = $stat;

                    //Ingresos Reales MyCP
                    $stat = new ownershipReservationStat();
                    $stat->setStatAccommodation($ownership)
                        ->setStatNomenclator($nomMyCPRealIncomes)
                        ->setStatValue( $mycpCommission)
                        ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                        ->setStatDateTo($reservation->getOwnResReservationToDate());
                    $result[] = $stat;

                    break;
                }
                case ownershipReservation::STATUS_OUTDATED:{
                    $stat = new ownershipReservationStat();
                    $stat->setStatAccommodation($ownership)
                        ->setStatNomenclator($nomOutdated)
                        ->setStatValue(1)
                        ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                        ->setStatDateTo($reservation->getOwnResReservationToDate());
                    $result[] = $stat;

                    $stat = new ownershipReservationStat();
                    $stat->setStatAccommodation($ownership)
                        ->setStatNomenclator($nomAvailable)
                        ->setStatValue(1)
                        ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                        ->setStatDateTo($reservation->getOwnResReservationToDate());
                    $result[] = $stat;
                    break;
                }
                case ownershipReservation::STATUS_AVAILABLE:
                case ownershipReservation::STATUS_AVAILABLE2:{
                    $stat = new ownershipReservationStat();
                    $stat->setStatAccommodation($ownership)
                        ->setStatNomenclator($nomAvailable)
                        ->setStatValue(1)
                        ->setStatDateFrom($reservation->getOwnResReservationFromDate())
                        ->setStatDateTo($reservation->getOwnResReservationToDate());
                    $result[] = $stat;
                    break;
                }
            }
        }

        $result = $this->calculateCommentsStats($ownership,$nomCommentsTotal, $result, $date);
        return $result;

    }

    function calculateCommentsStats($ownership, $nomenclator, $result, $date = null)
    {
        $em = $this->getEntityManager();
        $comments = $em->getRepository("mycpBundle:comment")->getCommentsByAccommodation($ownership, $date);

        foreach($comments as $comment) {
            //Total de comentarios recibidos por fecha
            $stat = new ownershipReservationStat();
            $stat->setStatAccommodation($ownership)
                ->setStatNomenclator($nomenclator)
                ->setStatValue(1)
                ->setStatDateFrom($comment->getComDate())
                ->setStatDateTo($comment->getComDate());
            $result[] = $stat;
        }

        return $result;
    }

    /**
     * Calculate daily incomes
     * @param $ownership
     * @param $reservation
     * @param $result
     * @return mixed
     */
    function calculateIncomes($ownership,$reservation, $result, $nomenclator= null){

        
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
                //case "Resúmen": $queryBuilder = $this->addSummaryNomenclatorWhere($nomenclator->getNomName(), $queryBuilder); break;
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
            case "Reserva Inmediata": $queryBuilder->andWhere("o.own_inmediate_booking = 1"); break;
        }

        return $queryBuilder;
    }
}
