<?php

namespace MyCp\CasaModuleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ownershipPhotoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', 'file', array(
              'mapped'=>false,
               'label'=>false,
              'attr'=>[
                  'class'=>'hide photo-input',
                  'accept'=>'image/*'
              ]
        ))
            ->add('description', 'text', array(
                'label'=>'Descripción',
                'mapped'=>false
            ))
//            ->add('own_pho_photo','hidden, ar')
            ->add('own_pho_id','hidden',
                array(
                    'mapped'=>false
                ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\ownershipPhoto'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mycp_mycpbundle_ownershipphoto';
    }
}
