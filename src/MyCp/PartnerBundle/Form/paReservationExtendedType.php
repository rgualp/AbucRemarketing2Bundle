<?php

namespace MyCp\PartnerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\NotBlank;

class paReservationExtendedType extends AbstractType
{
    private $translate;
    private $travelAgency;

    function __construct($trans_entity, $travelAgency)
    {
        $this->translate = $trans_entity;
        $this->travelAgency = $travelAgency;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('client', 'entity', [
                'class' => 'MyCp\PartnerBundle\Entity\paClient',
                'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->where("u.travelAgency = :travelAgency")
                            ->setParameter("travelAgency", $this->travelAgency->getId())
                            ->orderBy("u.fullname", "ASC")
                            ;
                    },
                'empty_data'  => null,
                'empty_value' => "",
                'property' => 'fullname',
                'required' => false,
                'multiple' => false])
            ->add('adults', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('email', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('children', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('country', 'entity', [
                'class' => 'MyCp\mycpBundle\Entity\country',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy("u.co_name", "ASC")
                        ;
                },
                'empty_data'  => null,
                'empty_value' => "",
                'required' => false,
                'multiple' => false])
            ->add('comments', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('number', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
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
