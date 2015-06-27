<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\FileIO;
use MyCp\mycpBundle\Helpers\Images;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * ownershipDescriptionLangRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ownershipDescriptionLangRepository extends EntityRepository {

    public function getDescriptionsByAccommodation(ownership $ownership, $langCode)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->select('odl')
            ->from("mycpBundle:ownershipDescriptionLang", "odl")
            ->join("odl.odl_id_lang", "lang")
            ->join("odl.odl_ownership", "own")
            ->where("own.own_id = :ownId")
            ->setParameter("ownId", $ownership->getOwnId())
            ->andWhere("lang.lang_code = :langCode")
            ->setParameter("langCode", strtoupper($langCode));

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getAccommodationsToTranslate($sourceLangCode, $targetLangsCode)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->select('o')
            ->from("mycpBundle:ownership", "o")
            ->where("(SELECT count(odl) FROM mycpBundle:ownershipDescriptionLang odl JOIN odl.odl_id_lang lang WHERE odl.odl_ownership = o.own_id AND lang.lang_code = :sourceLangCode) > 0")
            ->andWhere("(SELECT count(odl1) FROM mycpBundle:ownershipDescriptionLang odl1 JOIN odl1.odl_id_lang lang1 WHERE odl1.odl_ownership = o.own_id AND lang1.lang_code = :targetLangCode) = 0")
            ->setParameters(array("sourceLangCode" => strtoupper($sourceLangCode), "targetLangCode" => strtoupper($targetLangsCode)));

        return $qb->getQuery()->getResult();
    }
}
