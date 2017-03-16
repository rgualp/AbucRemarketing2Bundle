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

class effectiveMethodPaymentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contactName',null,array('label'=>'Nombre del contacto:','attr'=>array('class'=>'input-block-level')))
            ->add('identityNumber',null,array('label'=>'Número de identidad:','attr'=>array('class'=>'input-block-level')))
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\effectiveMethodPayment'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_effectivemethodpaymenttype';
    }
}
