<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ownershipStatRepository
 *
 */
class ownershipStatRepository extends EntityRepository
{

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
        $statDb = $em->getRepository("mycpBundle:ownershipStat")->findOneBy(array("stat_municipality" => $stat->getStatMunicipality()->getMunId(), "stat_nomenclator" => $stat->getStatNomenclator()->getNomId()));

        if($statDb === null)
            $statDb = new ownershipStat();

        $statDb->setStatNomenclator($stat->getStatNomenclator());
        $statDb->setStatMunicipality($stat->getStatMunicipality());
        $statDb->setStatValue($stat->getStatValue());

        $em->persist($stat);
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
            $nomTotal=$nomenclatorRepository->findOneBy(array('nom_name'=>'Resúmen'));
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
            $ownsRest=  $ownershipRepository->findBy(array('own_address_municipality'=>$municipality));
            foreach($nomenRooms as $ownCat){
                if(is_int($ownCat->getNomName())) {
                    $owns = $ownershipRepository->findBy(array('own_address_municipality' => $municipality, 'own_rooms_total' => (int)$ownCat->getNomName()));
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
}
