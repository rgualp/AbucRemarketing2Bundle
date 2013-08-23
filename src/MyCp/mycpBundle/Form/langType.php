<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class langType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lang_name',null,array('label'=>'Nombre:', 'attr'=>array('class'=>'input-block-level')))
            ->add('lang_code',null,array('label'=>'Siglas:','attr'=>array('class'=>'input-block-level')))
            ->add('lang_active',null,array('label'=>' ','attr'=>array('class'=>'checkbox_left')))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\lang'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_langtype';
    }
}
