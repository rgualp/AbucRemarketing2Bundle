<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
class emailDestinationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('destination', 'entity', array(
                'class' => 'MyCp\mycpBundle\Entity\destination',
                'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('d')->orderBy('d.des_name', 'ASC');
                    },
                'empty_data'  => null,
                'empty_value' => "",
                'property' => 'des_name',
                'required' => true,
                'multiple' => false
            ))
            ->add('content_es','textarea',array('required' => false,'attr'=>array('class'=>'input-block-level')))
            ->add('content_en','textarea',array('required' => false,'attr'=>array('class'=>'input-block-level')))
            ->add('content_de','textarea',array('required' => false,'attr'=>array('class'=>'input-block-level')))

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\emailDestination'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_emaildestination';
    }
}
