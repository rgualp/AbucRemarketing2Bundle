<?php

namespace MyCp\CasaModuleBundle\Form;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Form\AccomodationBookingModalityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ownershipStep1Type extends AbstractType
{
    private $province;

    function __construct($province)
    {
        $this->province = $province;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('own_name', 'text', array(
                'attr' => array(
                    'class' => 'form-control hide',
                    'placeholder' => 'Nombre de la casa'
                ),
            ))
            ->add('own_licence_number', 'text', array(
                    'attr' => array(
                        'class' => 'form-control hide',
                        'placeholder' => 'Número de licencia'
                    ),)
            )
            ->add('own_address_street', 'text', array(
                'label' => false,
                'required' => true,
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Calle'
                )
            ))
            ->add('own_address_number', 'text', array(
                'label' => false,
                'required' => true,
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Número'
                )
            ))
            ->add('own_address_between_street_1', 'text', array(
                'label' => false,
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Entre'
                )
            ))
            ->add('own_address_between_street_2', 'text', array(
                'label' => false,
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'y'
                )
            ))
//            ->add('own_phone_code')
            ->add('own_phone_number')
//             ->add('own_category')
            ->add('own_type', 'choice', array(
                'label' => 'Tipo de propiedad',
                'attr' => array(
                    'class' => 'form-control',
//                    'placeholder'=>'y'
                ),
                'choices' => array(
                    'Casa particular' => 'Casa particular',
                    'Apartamento' => 'Apartamento',
                    'Villa con piscina' => 'Villa con piscina',
                    'Penthouse' => 'Penthouse'
                )
            ))
            ->add('own_rental_type', 'choice', array(
                'label' => 'Tipo de renta',
                'attr' => array(
                    'class' => 'form-control',
//                    'placeholder'=>'y'
                ),
                'choices' => array(
                    'Propiedad completa' => 'Propiedad Completa',
                    'Por habitaciones' => 'Por habitaciones',

                )
            ))
            ->add('own_langs1', 'choice', array(
                'label' => 'Idiomas que se hablan en la casa',
                'attr' => array(
                    'class' => 'form-control',
//                    'placeholder'=>'y'),
                ),
                'choices' => array(
                    '1000' => 'Inglés',
                    '0100' => 'Alemán',
                    '0010' => 'Francés',
                    '0001' => 'Italiano'
                ),
                'mapped' => false
            ))
            ->add('own_langs', 'hidden', array())
            ->add('geolocate', 'text', array(
                'attr' => array(
                    'class' => 'form-control'
                ),
                'mapped' => false
            ))
            ->add('own_geolocate_x', 'hidden')
            ->add('own_geolocate_y', 'hidden')
            ->add('own_address_province')
            ->add('own_address_municipality')
            ->add('own_destination', 'entity', array(
                'class' => 'MyCp\mycpBundle\Entity\destination',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->select("distinct u")
                        ->join("u.locations", "location")
                        ->where("location.des_loc_province = :province")
                        ->orderBy("u.des_name", "ASC")
                        ->setParameter("province", $this->province->getProvId());
                },
                'property' => 'des_name',
                'required' => false,
                'multiple' => false
            ))
            ->add('ownAgencyWork', 'radio')
            ->add('ownModalityReservation', 'choice', array(
                'attr' => array(
                    'class' => 'form-control',
                ),
                'choices' => array(
                    'Reserva Inmediata' => 'Reserva Inmediata',
                    'Reserva Rápida' => 'Reserva Rápida',
                    'Por solicitudes' => 'Por solicitudes'
                )
            ));
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
