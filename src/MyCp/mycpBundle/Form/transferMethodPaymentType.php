<?php
namespace MyCp\mycpBundle\Form;

use MyCp\mycpBundle\Helpers\FormMode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class transferMethodPaymentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titular',null,array('label'=>'Titular:','attr'=>array('class'=>'input-block-level')))
            ->add('accountNumber',null,array('label'=>'Número de cuenta:','attr'=>array('class'=>'input-block-level')))
            ->add('accountType', 'entity', array(
                'label'=>'Tipo de cuenta:',
                'class' => 'MyCp\mycpBundle\Entity\nomenclator',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->innerJoin('n.translations', 't')
                        ->join("t.nom_lang_id_lang", "lang")
                        ->where("n.nom_category = 'bankAccountType'")
                        ->andWhere("lang.lang_code = 'ES'");
                },
                'property' => 'translations[0].nom_lang_description',
                'required' => true,
                'multiple' => false
            ))
            ->add('bankBranchName',null,array('label'=>'Sucursal bancaria:','attr'=>array('class'=>'input-block-level')))
            ->add('identityNumber',null,array('label'=>'Número de identidad:','attr'=>array('class'=>'input-block-level')))
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\transferMethodPayment'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_transfermethodpaymenttype';
    }
}
