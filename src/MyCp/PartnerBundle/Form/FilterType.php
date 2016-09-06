<?php

namespace MyCp\PartnerBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //TEXT ************************
            ->add('cas', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('own_name', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('code', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('from', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('to', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('destination', 'entity', array(
                'class' => 'MyCp\mycpBundle\Entity\destination',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')->orderBy('d.des_name', 'ASC');
                },
                'empty_data'  => null,
                'empty_value' => "",
                'property' => 'des_name',
                'required' => true,
                'multiple' => false
            ))
            ->add('date', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('room_number', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('adults_number', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('booking_code', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('client_dates', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('booking_date', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
       /* $resolver->setDefaults(array(
            'data_class' => 'MyPaladar\BackendBundle\Entity\Bussiness'
        ));*/
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'booking_filter';
    }
}
