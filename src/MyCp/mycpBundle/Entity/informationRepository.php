<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * informationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class informationRepository extends EntityRepository {

    function new_information($post) {
        $em = $this->getEntityManager();
        $languages = $em->getRepository('mycpBundle:lang')->findAll();
        $nomenclator = $em->getRepository('mycpBundle:nomenclator')->find($post['information_type']);
        $information = new information();
        $information->setInfoFixed(0);
        $information->setInfoIdNom($nomenclator);
        $em->persist($information);

        foreach ($languages as $language) {
            $information_lang = new informationLang();
            $information_lang->setInfoLangLang($language);
            $information_lang->setInfoLangContent($post['info_content_' . $language->getLangId()]);
            $information_lang->setInfoLangName($post['info_name_' . $language->getLangId()]);
            $information_lang->setInfoLangInfo($information);
            $em->persist($information_lang);
        }
        $em->flush();
    }

    function edit_information($post) {
        $em = $this->getEntityManager();
        $languages = $em->getRepository('mycpBundle:lang')->findAll(); ///edit_information

        $information = $em->getRepository('mycpBundle:information')->find($post['edit_information']);
        $nomenclator = $em->getRepository('mycpBundle:nomenclator')->find($post['information_type']);
        $information->setInfoIdNom($nomenclator);
        $em->persist($information);

        foreach ($languages as $lang) {
            $information_lang = $em->getRepository('mycpBundle:informationLang')->findOneBy(array('info_lang_info' => $post['edit_information'], 'info_lang_lang' => $lang->getLangId()));
            //$information_lang=$information_lang[0];

            if ($information_lang != null) {
                $information_lang->setInfoLangName($post['info_name_' . $lang->getLangId()]);
                $information_lang->setInfoLangContent($post['info_content_' . $lang->getlangId()]);
                $em->persist($information_lang);
            } else {
                $information_lang = new informationLang();
                $information_lang->setInfoLangLang($lang);
                $information_lang->setInfoLangContent($post['info_content_' . $lang->getLangId()]);
                $information_lang->setInfoLangName($post['info_name_' . $lang->getLangId()]);
                $information_lang->setInfoLangInfo($information);
                $em->persist($information_lang);
            }
        }
        $em->flush();
    }

    function list_information($information_type, $language) {
        $em = $this->getEntityManager();
        $query_string = "SELECT il FROM mycpBundle:informationLang il
                        JOIN il.info_lang_info info
                        JOIN il.info_lang_lang lang
                        JOIN info.info_id_nom nom
                        WHERE lang.lang_code = :language
                        AND nom.nom_name = :information_type";

        return $em->createQuery($query_string)->setParameters(array(
            'information_type'=> $information_type, 'language' =>$language))
                ->getResult();
    }

    function category_names($informations_lang, $language_code) {
        $em = $this->getEntityManager();
        $nomenclators = array();

        foreach ($informations_lang as $info) {
            $nomenclators[$info->getInfoLangId()] = $em->getRepository('mycpBundle:nomenclator')->get_by_id($info->getInfoLangInfo()->getInfoIdNom()->getNomId(), $language_code);
        }

        return $nomenclators;
    }

    function get_numbers()
    {
        $em = $this->getEntityManager();
        $query_string = 'SELECT COUNT(accommodations) AS acc,(SELECT count(rms) FROM mycpBundle:room rms ) as rooms,(SELECT count(lng) FROM mycpBundle:lang lng WHERE lng.lang_active=1 ) as langs  FROM mycpBundle:ownership accommodations';
        return $em->createQuery($query_string)->getResult();
    }

    function get_information_about_us($language_code)
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT info_lang
        FROM mycpBundle:informationLang info_lang
        JOIN info_lang.info_lang_info info
        JOIN info_lang.info_lang_lang lang
        WHERE info_lang.info_lang_info = info.info_id
        AND info.info_id_nom = 1
        AND lang.lang_code = :language_code";
        return $em->createQuery($query_string)->setParameter('language_code', $language_code)->getResult();
    }

}
