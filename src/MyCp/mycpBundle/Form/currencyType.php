<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class currencyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('curr_name',null,array('label'=>'Nombre:'))
            ->add('curr_code',null,array('label'=>'Código:'))
            ->add('curr_symbol',null,array('label'=>'Símbolo:'))
            ->add('curr_cuc_change',null,array('label'=>'Cambio (Respecto a CUC):'))
            ->add('curr_default',null,array('label'=>'Moneda por defecto'))
            ->add('curr_site_price_in',null,array('label'=>'Los precios están almacenados en'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\currency'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_currencytype';
    }
}
