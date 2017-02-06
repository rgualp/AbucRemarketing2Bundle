<?php

namespace MyCp\mycpBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class pendingPaymentAgencyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', 'entity', array(
                    'label'=>'Estado:',
                    'class' => 'MyCp\mycpBundle\Entity\nomenclator',
                    'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('n')
                                ->innerJoin('n.translations', 't')
                                ->join("t.nom_lang_id_lang", "lang")
                                ->where("n.nom_category = 'paymentPendingStatus'")
                                ->andWhere("lang.lang_code = 'ES'");
                        },
                    'property' => 'translations[0].nom_lang_description',
                    'required' => true,
                    'multiple' => false
                ))
        ;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'mycp_mycpbundle_payment_pending_agency';
    }
}
