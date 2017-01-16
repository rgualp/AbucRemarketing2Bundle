<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class cancelPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'entity', array(
                    'label' => 'Tipo de Cancelación:',
                'class' => 'MyCp\mycpBundle\Entity\cancelType',
                'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('d')->orderBy('d.cancel_name', 'ASC');
                    },
                'empty_data'  => null,
                'empty_value' => "",
                'property' => 'cancel_name',
                'required' => true,
                'multiple' => false
            ))
            ->add('give_tourist', 'checkbox', array('label' => 'Devolver dinero a turista:', 'attr' => array('checked' => true)))
            ->add('cancel_date','text',array('label'=>'Fecha de cancelación de la reserva:','attr'=>array('class'=>'input-block-level datepicker')))
            ->add('reason','textarea',array('label' => 'Motivos:','required' => false,'attr'=>array('class'=>'input-block-level')))

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\cancelPayment'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_cancelpayment';
    }
}
