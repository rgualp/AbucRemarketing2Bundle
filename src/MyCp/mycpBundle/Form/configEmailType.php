<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class configEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject_es','textarea',array('required' => false,'attr'=>array('class'=>'input-block-level')))
            ->add('subject_en','textarea',array('required' => false,'attr'=>array('class'=>'input-block-level')))
            ->add('subject_de','textarea',array('required' => false,'attr'=>array('class'=>'input-block-level')))
            ->add('introduction_es','textarea',array('required' => false,'attr'=>array('class'=>'input-block-level')))
            ->add('introduction_en','textarea',array('required' => false,'attr'=>array('class'=>'input-block-level')))
            ->add('introduction_de','textarea',array('required' => false,'attr'=>array('class'=>'input-block-level')))
            ->add('foward_es','textarea',array('required' => false,'attr'=>array('class'=>'input-block-level')))
            ->add('foward_en','textarea',array('required' => false,'attr'=>array('class'=>'input-block-level')))
            ->add('foward_de','textarea',array('required' => false,'attr'=>array('class'=>'input-block-level')))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\configEmail'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_configemail';
    }
}
