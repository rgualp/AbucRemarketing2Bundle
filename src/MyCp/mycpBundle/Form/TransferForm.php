<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TransferForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from',null,array('label'=>'Origen:', 'attr'=>array('class'=>'input-block-level')))
            ->add('to',null,array('label'=>'Destino:','attr'=>array('class'=>'input-block-level')))
            ->add('price',null,array('label'=>'Precio CUC','attr'=>array('class'=>'input-block-level')))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\transfer'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_transfer';
    }
}
