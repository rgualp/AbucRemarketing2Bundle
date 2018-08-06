<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 1/08/18
 * Time: 10:19
 */

namespace MyCp\FrontEndBundle\Service;


use Doctrine\ORM\EntityManager;

class DestinationService
{

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function convertDistinationByLang($destinationName, $lang)
    {
        if (strpos($destinationName, '-') !== false) {
            $destinationName = ucwords(implode(' ', explode('-', $destinationName)));
        }
        $result = $this->em->getRepository('mycpBundle:destinationLang')->findOneBy(array('des_lang_name' => $destinationName));
        $language = $this->em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => strtoupper($lang)));
        if (!is_null($result) && !is_null($language)) {
            $query = $this->em->getRepository('mycpBundle:destinationLang')
                ->createQueryBuilder('destination_lang');
            $result = $query->select('destination_lang.des_lang_name')->where('destination_lang.des_lang_lang = :lang')
                ->andWhere('destination_lang.des_lang_destination = :destination')
                ->setParameters(array('lang' => $language->getLangId(), 'destination' => $result->getDesLangDestination()))
                ->getQuery()
                ->getResult();
            if (is_array($result)) {
                $destinationName = $result[0]['des_lang_name'];
            }
        }
        $destinationName = strtolower(str_replace(' ', '-', $destinationName));
        return $destinationName;
    }
}