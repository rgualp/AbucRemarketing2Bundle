<?php

namespace MyCp\mycpBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class pendingPaytouristType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pay_amount','text',array(
                'label'=>'Cantidad pagada:',
                'constraints'=>array(new NotBlank())
            ))

            ->add('payment_date',null,array(
                'widget'=>'single_text',
                'format'=>'dd/MM/yyyy',
                'label'=>'Fecha de pago (dia/mes/año - dd/mm/yyyy):',
                'attr'=>array('class'=>'input-block-level datepicker_textbox', "style" => "width: 30%")
            ))
            ->add('reason','textarea',array(
                    'label'=>'Motivos:',
                    'attr'=>array('class'=>'textarea', "style" => "width: 80%")
                ))
        ;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'mycp_mycpbundle_payment_pending_tourist';
    }
}
