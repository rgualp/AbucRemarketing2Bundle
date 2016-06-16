<?php

namespace MyCp\mycpBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ownershipPaymentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accommodation', 'entity', array(
                'label'=>'Alojamiento:',
                'class' => 'MyCp\mycpBundle\Entity\ownership',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('o')->orderBy('length(o.own_mcp_code)', 'ASC')->addOrderBy('o.own_mcp_code', 'ASC');
                },
                'property' => 'codeAndName',
                'required' => true,
                'multiple' => false,
                'attr' => array("class" => "select input-block-level")
            ))
            ->add('service', 'entity', array(
                'label'=>'Servicio a pagar:',
                'class' => 'MyCp\mycpBundle\Entity\mycpService',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')->orderBy('s.name', 'ASC');
                },
                'property' => 'name',
                'required' => true,
                'multiple' => false
            ))
            ->add('method', 'entity', array(
                'label'=>'Método de pago:',
                'class' => 'MyCp\mycpBundle\Entity\nomenclator',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->innerJoin('n.translations', 't')
                        ->join("t.nom_lang_id_lang", "lang")
                        ->where("n.nom_category = 'accommodationPaymentType'")
                        ->andWhere("lang.lang_code = 'ES'");
                },
                'property' => 'translations[0].nom_lang_description',
                'required' => true,
                'multiple' => false
            ))
            ->add('payed_amount','text',array(
                'label'=>'Cantidad pagada:',
                'constraints'=>array(new NotBlank())
            ))
            ->add('payment_date',null,array(
                'widget'=>'single_text',
                'format'=>'dd/MM/yyyy',
                'label'=>'Fecha de pago (dia/mes/año - dd/mm/yyyy):',
                'attr'=>array('class'=>'input-block-level datepicker_textbox', "style" => "width: 206px")
            ))
        ;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'mycp_mycpbundle_ownership_payment';
    }
}
