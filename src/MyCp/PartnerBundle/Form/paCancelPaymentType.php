<?php

namespace MyCp\PartnerBundle\Form;

use MyCp\mycpBundle\Entity\nomenclatorRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class paCancelPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'entity', array(
                    'label' => 'Tipo de Cancelación:',
                'class' => 'MyCp\mycpBundle\Entity\nomenclator',
                'query_builder' => function (nomenclatorRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->innerJoin('n.translations', 't')
                        ->join("t.nom_lang_id_lang", "lang")
                        ->where("n.nom_category = 'agencyCancelPaymentType'")
                        ->andWhere("lang.lang_code = 'ES'");
                    },
                'empty_data'  => null,
                'empty_value' => "",
                'property' => 'translations[0].nom_lang_description',
                'required' => true,
                'multiple' => false
            ))
            ->add('give_agency', 'checkbox', array('label' => 'Devolver dinero a agencia:', 'attr' => array('checked' => true)))
            ->add('cancel_date',null,array(
                    'widget'=>'single_text',
                    'format'=>'dd/MM/yyyy',
                    'label'=>'Fecha de cancelación de la reserva: (dia/mes/año - dd/mm/yyyy):',
                    'attr'=>array('class'=>'input-block-level datepicker_textbox', "style" => "width: 30%")
                ))
            ->add('reason','textarea',array(
                    'label'=>'Motivos:',
                    'attr'=>array('class'=>'textarea', "style" => "width: 80%")
                ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\PartnerBundle\Entity\paCancelPayment'
        ));
    }

    public function getName()
    {
        return 'mycp_partnerbundle_pacancelpayment';
    }
}
