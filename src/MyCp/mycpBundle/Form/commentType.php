<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class commentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('com_ownership_code',"text",array(
                'label'=>'Código Alojamiento:','attr'=>array('class'=>'input-block-level')))
            ->add('com_user_email',"text",array('label'=>'Correo Usuario:','attr'=>array('class'=>'input-block-level')))
            ->add('com_comments','textarea',array('label'=>'Comentario:','attr'=>array('class'=>'input-block-level')))
            ->add('com_rate','choice', array('choices'=>array('1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5),'label'=>'Valoración','empty_value'=>'','attr'=>array('class'=>'input-block-level')))
            ->add('com_public',null,array('label'=>'Publicado en el sitio:'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\comment'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_commenttype';
    }
}
