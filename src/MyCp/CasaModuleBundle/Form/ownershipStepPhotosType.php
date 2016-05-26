<?php

namespace MyCp\CasaModuleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ownershipStepPhotosType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('photos', 'collection', array(
                'allow_add'=>true,
                'allow_delete'=>true,
                'by_reference'=>false,
                'type'=> new ownershipPhotoType(),
                'label'=>false,
//                'prototype' => true,
//                'prototype_name' => 'photo__name__',
//                'prototype_name' => 'photo__name__',

            ))


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
        return 'mycp_mycpbundle_ownership_step_photos';
    }
}
