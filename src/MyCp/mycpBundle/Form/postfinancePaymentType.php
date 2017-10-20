<?php
namespace MyCp\mycpBundle\Form;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\FormMode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class postfinancePaymentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('created',null,array('label'=>'Fecha y hora de pago:','attr'=>array('class'=>'input-block-level', "required" =>"true")))
            ->add('order_id',null,array('label'=>'Id Booking:','attr'=>array('class'=>'input-block-level', "required" =>"true")))
            ->add('acceptance',null,array('label'=>'Código de aceptación:','attr'=>array('class'=>'input-block-level', "required" =>"true")))
            ->add('payment_reference_id',null,array('label'=>'Referencia del pago:','attr'=>array('class'=>'input-block-level', "required" =>"true")))
            ->add('status',null,array('label'=>'Número del estado:','attr'=>array('class'=>'input-block-level', "required" =>"true")))
            ->add('amount',null,array('label'=>'Cantidad pagada:','attr'=>array('class'=>'input-block-level', "required" =>"true")))
            ->add('currency',null,array('label'=>'Moneda:','attr'=>array('class'=>'input-block-level', "required" =>"true")))
            ->add('payment_method',null,array('label'=>'Método de pago:','attr'=>array('class'=>'input-block-level', "required" =>"true")))
            ->add('card_brand',null,array('label'=>'Tipo de tarjeta:','attr'=>array('class'=>'input-block-level', "required" =>"true")))
            ->add('masked_card_number',null,array('label'=>'Número de tarjeta enmascarado:','attr'=>array('class'=>'input-block-level', "required" =>"true")))
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\postfinancePayment'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_postfinancepaymenttype';
    }
}
