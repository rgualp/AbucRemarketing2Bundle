<?php

namespace MyCp\CasaModuleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ownershipStep1Type extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('own_name', 'hidden')
            ->add('own_licence_number','hidden')
            ->add('own_address_street','text', array(
                'label'=>false,
                'attr'=>[
                    'class'=>'form-control',
                    'placeholder'=>'Calle'
                ]
            ))
            ->add('own_address_number','text', array(
                'label'=>false,
                'attr'=>[
                    'class'=>'form-control',
                    'placeholder'=>'Número'
                ]
            ))
            ->add('own_address_between_street_1','text', array(
                'label'=>false,
                'attr'=>[
                    'class'=>'form-control',
                    'placeholder'=>'Entre'
                ]
            ))
            ->add('own_address_between_street_2','text', array(
                'label'=>false,
                'attr'=>[
                    'class'=>'form-control',
                    'placeholder'=>'y'
                ]
            ))
//            ->add('own_phone_code')
//            ->add('own_phone_number')
//             ->add('own_category')
            ->add('own_type')
            ->add('own_langs')
            ->add('geolocate', 'text', array(
                'attr'=>[
                  'class'=>'form-control'
                ],
                'mapped'=>false
            ))
            ->add('own_geolocate_x','hidden')
            ->add('own_geolocate_y', 'hidden')
//            ->add('own_address_province')
//            ->add('own_address_municipality')
            ->add('own_destination')

        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\ownership'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mycp_mycpbundle_ownership_step1';
    }
}
