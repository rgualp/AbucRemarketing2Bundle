<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;

class unavailabilityDetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ud_from_date',null,array('widget'=>'single_text','format'=>'dd/MM/yyyy', 'label'=>'Desde:', 'attr'=>array('class'=>'input-block-level datepicker')))
            ->add('ud_to_date',null,array('widget'=>'single_text','format'=>'dd/MM/yyyy','label'=>'Hasta:','attr'=>array('class'=>'input-block-level datepicker')))
            ->add('ud_reason',null,array('label'=>'Motivo','attr'=>array('class'=>'input-block-level')))
        ;
        
        /*
         * add('reservation_from_date',null,array('label'=>'Fecha de entrada:','attr'=>array('class'=>'input-block-level datepicker')));
         */
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\unavailabilityDetails'
        ));
    }
    
    /*public function getDefaultOptions(array $options)
    {
        $array=array();
        $array['ud_from_date']= array(new NotBlank());
        $array['ud_to_date']= array(new NotBlank());
        $array['ud_reason']= array(new NotBlank());
        $collectionConstraint = new Collection($array);

        return array('validation_constraint' => $collectionConstraint);
    }*/

    public function getName()
    {
        return 'mycp_mycpbundle_unavailabilitydetailstype';
    }
}
