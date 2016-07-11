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
            ->add('own_name', 'text', [
                'attr'=>[
                    'class'=>'form-control hide',
                    'placeholder'=>'Nombre de la casa'
                ],
            ])
            ->add('own_licence_number', 'text', [
                'attr'=>[
                    'class'=>'form-control hide',
                    'placeholder'=>'Número de licencia'
                ],
            ])
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
            ->add('own_phone_number')
//             ->add('own_category')
            ->add('own_type','choice', array(
                'label'=>'Tipo de propiedad',
                'attr'=>[
                    'class'=>'form-control',
//                    'placeholder'=>'y'
                ],
                'choices'=>array(
                    'Casa particular'=>'Casa particular',
                    'Apartamento'=>'Apartamento',
                    'Propiedad completa'=>'Propiedad completa',
                    'Villa con piscina'=>'Villa con piscina',
                    'Penthouse'=>'Penthouse'
                )
            ))
            ->add('own_rental_type','choice', array(
                'label'=>'Tipo de renta',
                'attr'=>[
                    'class'=>'form-control',
//                    'placeholder'=>'y'
                ],
                'choices'=>array(
                    'Propiedad completa'=>'Propiedad completa',
                    'Por habitaciones'=>'Por habitaciones',

                )
            ))
            ->add('own_langs1','choice', array(
                'label'=>'Idiomas que se hablan en la casa',
                'attr'=>[
                    'class'=>'form-control',
//                    'placeholder'=>'y'
                ],
                'choices'=>array(
                    '1000'=>'Inglés',
                    '0100'=>'Alemán',
                    '0010'=>'Francés',
                    '0001'=>'Italiano'
                ),
                'mapped'=>false
            ))
            ->add('own_langs','hidden', array(
            ))
            ->add('geolocate', 'text', array(
                'attr'=>[
                  'class'=>'form-control'
                ],
                'mapped'=>false
            ))
            ->add('own_geolocate_x','hidden')
            ->add('own_geolocate_y', 'hidden')
            ->add('own_address_province')
            ->add('own_address_municipality')
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
