<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * metaTagRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class metaTagRepository extends EntityRepository
{
    function getAll()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT m.meta_id,
            m.meta_section,
            m.meta_title,
            p.meta_title as parent_title,
            (SELECT count(ml) FROM mycpBundle:metaLang ml WHERE ml.meta_tag = m.meta_id)
            FROM mycpBundle:metatag m
            JOIN m.meta_parent p
            ORDER BY m.meta_section ASC");
        return $query->getResult();
    }

    function getMetaLangs($meta_id)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ml FROM mycpBundle:metaLang ml WHERE ml.meta_tag = :$meta_id");
        return $query->getResult();
    }

    function getMetas($section_id, $lang_code)
    {
        $em = $this->getEntityManager();
        $meta = $em->find('mycpBundle:metaTag', $section_id);
        return $this->getMetasRecursive($em, $meta, $lang_code);
    }

    private function getMetasRecursive($entity_manager, $meta, $lang_code)
    {
        $query = $entity_manager->creatQuery("SELECT ml
            FROM mycpBundle:metaLang ml
            JOIN ml.meta_lang_lang lang
            WHERE ml.meta_tag = :meta_id
              AND lang.lang_code = :lang_code");

        $meta_lang = $query->setParameter('meta_id', $meta->getMetaId())
                           ->setParameter('lang_code', strtoupper($lang_code))
                           ->getOneOrNullResult();

        if($meta_lang != null || $meta->getMetaParent() == null)
            return $meta_lang;
        else

            return $this->getMetasRecursive($em, $meta->getMetaParent(), $lang_code);
    }
}

