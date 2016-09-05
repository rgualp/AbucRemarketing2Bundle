<?php

namespace MyCp\PartnerBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FilterOwnershipType extends AbstractType
{
    private $translate;

    function __construct($trans_entity)
    {
        $this->translate = $trans_entity;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        for($i=0;$i<=10;$i++){
            $huesp[$i]=$i;
        }
        for($i=0;$i<=10;$i++){
            $child[$i]=$i;
        }
        for($i=0;$i<100;$i++){
            $age[$i]=$i;
        }
        for($i=1;$i<=6;$i++){
            if($i==1)
            $room[$i]=$i.' '.$this->translate->trans('ROOM_SINGULAR');
            else
                $room[$i]=$i.' '.$this->translate->trans('ROOM_PLURAL');
        }
        $builder
            ->add('destination', 'entity', array(
                'class' => 'MyCp\mycpBundle\Entity\destination',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')->orderBy('d.des_name', 'ASC');
                },
                'empty_data'  => null,
                'empty_value' => "",
                'property' => 'des_name',
                'required' => false,
                'multiple' => false
            ))
            ->add('arrival', 'text', array('required' => false, 'attr' => array('class' => 'form-control input-date')))
            ->add('exit', 'text', array('required' => false, 'attr' => array('class' => 'form-control input-date')))
            ->add('huesp','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$huesp
            ))
            ->add('child','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$child
            ))
            ->add('age','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$age
            ))
            ->add('room','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$room
            ))
            ->add('own_category','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'multiple' => true,
                'choices'=>array('Económica'=>$this->translate->trans('Económica'),'Rango medio'=>$this->translate->trans('Rango medio'),'Premium'=>$this->translate->trans('Premium'))
            ))
            ->add('own_type','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'multiple' => true,
                'choices'=>array('Penthouse'=>$this->translate->trans('Penthouse'),'Villa con piscina'=>$this->translate->trans('Villa con piscina'),'Apartamento'=>$this->translate->trans('Apartamento'),'Propiedad completa'=>$this->translate->trans('Propiedad completa'),'Casa particular'=>$this->translate->trans('Casa particular'))
            ))
            ->add('own_beds_total','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6)
            ))
            ->add('room_type','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>array('Habitación individual'=>$this->translate->trans('SINGLE_ROOM_FILTER'),'Habitación doble'=>$this->translate->trans('DOUBLE_ROOM_FILTER'),'Habitación doble (Dos camas)'=>$this->translate->trans('DOUBLE_2_BEDS_FILTER'),'Habitación Triple'=>$this->translate->trans('TRIPLE_ROOM_FILTER'))
            ))
            ->add('room_bathroom','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>array('Interior privado'=>$this->translate->trans('INNER_PRIVATE_BATHROOM_FILTER'),'Exterior privado'=>$this->translate->trans('OUTER_PRIVATE_BATHROOM_FILTER'),'Compartido'=>$this->translate->trans('SHARED_BATHROOM_FILTER'))
            ))
            ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'requests_ownership_filter';
    }
}
