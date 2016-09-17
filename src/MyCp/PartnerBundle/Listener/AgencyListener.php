<?php
namespace MyCp\PartnerBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use MyCp\PartnerBundle\Entity\paTravelAgency;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class AgencyListener {

    /*private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }*/

    public function preUpdate($entity, PreUpdateEventArgs $args)
    {
       // $user = $this->container->get('security.context')->getToken()->getUser();
        $entity->setModified(new \DateTime())
            //->setModifiedBy($user)
        ;
    }

    public function prePersist(paTravelAgency $entity, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $country=$em->getRepository('mycpBundle:country')->find($entity->getCountry());
        $this->generateAutomaticCode($em, $entity,$country );

        /*if($entity->getCreatedBy() == null) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            $entity->setCreatedBy($user);
        }*/
    }
    private function generateAutomaticCode($em, $entity, $country)
    {
            $prefix='P';
            $code = $country->getCoCode();
            $query = "SELECT MAX(SUBSTRING(o.code, 5)*1) AS code FROM PartnerBundle:paTravelAgency o WHERE o.code LIKE :mycode";
            $codeMCP = $em->createQuery($query)->setParameter('mycode', "%" . $prefix.$code . "%")->getSingleScalarResult();
            if (count($codeMCP)) {
                $number = (int)$codeMCP;
                $number++;

                $str_number = ''.$number;
                if($number < 100){
                    $str_number = str_pad($str_number, 3, "0", STR_PAD_LEFT);
                }

                $code = $prefix.$code . $str_number;
                $entity->setCode($code);
            }
        else{
            $codeMCP=0;
            $number = (int)$codeMCP;
            $number++;

            $str_number = ''.$number;
            if($number < 100){
                $str_number = str_pad($str_number, 3, "0", STR_PAD_LEFT);
            }

            $code = $prefix.$code . $str_number;
            $entity->setCode($code);
        }
    }
} 