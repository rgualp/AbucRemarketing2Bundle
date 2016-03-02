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
            ->add('com_ownership',"entity",array(
                'class' => 'MyCp\mycpBundle\Entity\ownership',
                'query_builder' => function($repository) {$qb = $repository->createQueryBuilder('o');
                    return
                    $qb->orderBy('SUBSTRING(o.own_mcp_code, 1, 2)', "ASC")
                    ->add('orderBy', $qb->expr()->abs('SUBSTRING(o.own_mcp_code FROM 3)'), "ASC"); },
                'label'=>'Propiedad:','empty_value'=>'', 'attr'=>array('class'=>'input-block-level')))
            ->add('com_user',"entity",array(
                'class' => 'MyCp\mycpBundle\Entity\user',
                'query_builder' => function($repository) { return $repository->createQueryBuilder('u')->orderBy('u.user_user_name', 'ASC', 'u.user_last_name'); },
                'label'=>'Usuario:','empty_value'=>'','attr'=>array('class'=>'input-block-level')))
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
