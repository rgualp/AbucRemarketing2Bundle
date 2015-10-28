<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * metaTagRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class metaTagRepository extends EntityRepository {

    function getAll() {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT m.meta_id,
            m.meta_section,
            m.meta_title,
           (SELECT max(p.meta_title) FROM mycpBundle:metatag p WHERE p.meta_id = m.meta_parent) as parent_title,
            (SELECT count(ml) FROM mycpBundle:metaLang ml WHERE ml.meta_tag = m.meta_id) as langs_total
            FROM mycpBundle:metatag m
            ORDER BY m.meta_section ASC");
        return $query->getResult();
    }

    function getMetaLangs($meta_id) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ml FROM mycpBundle:metaLang ml WHERE ml.meta_tag = $meta_id");
        return $query->getResult();
    }

    function getMetas($section_id, $lang_code) {
        $em = $this->getEntityManager();
        return $em->createQuery("SELECT ml
            FROM mycpBundle:metaLang ml
            JOIN ml.meta_lang_lang lang
            JOIN ml.meta_tag m
            WHERE m.meta_section = :meta_section
              AND lang.lang_code = :lang_code")
            ->setParameter('meta_section', $section_id)
            ->setParameter('lang_code', $lang_code)
            ->getOneOrNullResult();
    }

    /*private function getMetasRecursive($entity_manager, $meta, $lang_code) {
        if ($meta != null) {
            $query = $entity_manager->createQuery("SELECT ml
            FROM mycpBundle:metaLang ml
            JOIN ml.meta_lang_lang lang
            JOIN ml.meta_tag m
            WHERE m.meta_section = :meta_section
              AND lang.lang_code = :lang_code");

            $meta_lang = $query->setParameter('meta_section', $meta->getMetaSection())
                    ->setParameter('lang_code', $lang_code)
                    ->getOneOrNullResult();

            if ($meta_lang != null || $meta->getMetaParent() == null)
                return $meta_lang;
            else
                return $this->getMetasRecursive($entity_manager, $meta->getMetaParent(), $lang_code);
        }
        else
            return null;
    }*/

}

