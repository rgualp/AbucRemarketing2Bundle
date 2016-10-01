<?php

namespace MyCp\PartnerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\NotBlank;

class paReservationType extends AbstractType
{
    private $translate;

    function __construct($trans_entity)
    {
        $this->translate = $trans_entity;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('client', 'entity', [
                'class' => 'MyCp\PartnerBundle\Entity\paClient',
                'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u');
                    },
                'empty_data'  => null,
                'empty_value' => "",
                'property' => 'fullname',
                'required' => false,
                'multiple' => false])
            ->add('adults', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('email', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('children', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        /*$resolver->setDefaults(array(
            'data_class' => 'MyCp\PartnerBundle\Entity\paTravelAgency'
        ));*/
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'partner_reservation';
    }
}
