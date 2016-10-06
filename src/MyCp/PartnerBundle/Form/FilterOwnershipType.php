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
                'required' => false,
                'choices'=>$huesp
            ))
            ->add('child','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'required' => false,
                'choices'=>$child
            ))
            ->add('age','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'required' => false,
                'choices'=>$age
            ))
            ->add('room','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'required' => false,
                'choices'=>$room
            ))
            ->add('own_category','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'multiple' => true,
                'required' => false,
                'choices'=>array('Económica'=>/** @Ignore */$this->translate->trans('Económica'),'Rango medio'=>/** @Ignore */$this->translate->trans('Rango medio'),'Premium'=>/** @Ignore */$this->translate->trans('Premium'))
            ))
            ->add('own_type','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'multiple' => true,
                'required' => false,
                'choices'=>array('Penthouse'=>/** @Ignore */$this->translate->trans('Penthouse'),'Villa con piscina'=>/** @Ignore */$this->translate->trans('Villa con piscina'),'Apartamento'=>/** @Ignore */$this->translate->trans('Apartamento'),'Propiedad completa'=>/** @Ignore */$this->translate->trans('Propiedad completa'),'Casa particular'=>/** @Ignore */$this->translate->trans('Casa particular'))
            ))
            ->add('own_beds_total','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'required' => false,
                'choices'=>array(1=>/** @Ignore */1,2=>/** @Ignore */2,3=>/** @Ignore */3,4=>/** @Ignore */4,5=>/** @Ignore */5,6=>/** @Ignore */6)
            ))
            ->add('room_type','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'required' => false,
                'choices'=>array('Habitación individual'=>/** @Ignore */$this->translate->trans('SINGLE_ROOM_FILTER'),'Habitación doble'=>/** @Ignore */$this->translate->trans('DOUBLE_ROOM_FILTER'),'Habitación doble (Dos camas)'=>/** @Ignore */$this->translate->trans('DOUBLE_2_BEDS_FILTER'),'Habitación Triple'=>/** @Ignore */$this->translate->trans('TRIPLE_ROOM_FILTER'))
            ))
            ->add('room_bathroom','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'required' => false,
                'choices'=>array('Interior privado'=>/** @Ignore */$this->translate->trans('INNER_PRIVATE_BATHROOM_FILTER'),'Exterior privado'=>/** @Ignore */$this->translate->trans('OUTER_PRIVATE_BATHROOM_FILTER'),'Compartido'=>/** @Ignore */$this->translate->trans('SHARED_BATHROOM_FILTER'))
            ))
            ->add('own_award', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('own_inmediate_booking', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('room_climatization', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('own_others_pets', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('room_audiovisuals', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('room_kids', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('room_smoker', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('pool', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('room_balcony', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('room_terraza', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('room_courtyard', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('laundry', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('parking', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('jacuzzy', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('own_others_internet', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('dinner', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('breakfast', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('room_safe', 'checkbox', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('own_others_languages','choice', array(
                'attr'=>[
                    'class'=>'form-control',
                ],
                'choices'=>array(
                    '1___'=>'Inglés',
                    '__1_'=>'Alemán',
                    '_1__'=>'Francés',
                    '___1'=>'Italiano'
                ),
                'mapped'=>false,
                'empty_data'  => null,
                'empty_value' => "",
                'required' => false,
                'multiple' => true
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
