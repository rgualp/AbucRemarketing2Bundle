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
            /*->add('multiple', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))*/
            ->add('code', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('name', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('accommodation', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('arrival', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('exit', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
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
            ->add('request', 'text', array('required' => true, 'attr' => array('class' => 'form-control')));
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
        return 'requests_filter';
    }
}
